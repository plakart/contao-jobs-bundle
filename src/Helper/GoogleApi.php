<?php

declare(strict_types=1);

/*
 * This file is part of Contao Jobs Bundle.
 *
 * (c) plakart GmbH & Co. KG (https://plakart.de)
 * author Jannik Nölke (https://jaynoe.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plakart\ContaoJobsBundle\Helper;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Environment;
use Contao\PageModel;
use Google_Client;
use Google_Service_Indexing;
use Google_Service_Indexing_UrlNotification;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Plakart\ContaoJobsBundle\Routing\ReaderPageResolver;
use Throwable;

class GoogleApi
{
    private const TYPE_URL_UPDATED = 'URL_UPDATED';
    private const TYPE_URL_DELETED = 'URL_DELETED';

    private ?Google_Client $client = null;
    private ?string $serviceAccountPath = null;
    private bool $analysisMode = false;
    private bool $initialized = false;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly ReaderPageResolver $readerPageResolver,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire(service: 'monolog.logger.contao')]
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Sets up the Google client on first use, so that merely instantiating the
     * service (e.g. as a dependency of a DCA callback) stays cheap.
     */
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;
        $this->framework->initialize();
        $this->analysisMode = $this->isAnalysisMode();

        if (!Config::get('gfjApi')) {
            return;
        }

        $jsonFileName = trim((string) (Config::get('gfjJsonFileName') ?? ''));
        $json = $this->projectDir . '/var/' . $jsonFileName;
        $this->serviceAccountPath = $json;

        if ('' === $jsonFileName) {
            $this->log(LogLevel::ERROR, 'Google Indexing API disabled: no JSON filename configured.', ContaoContext::ERROR);
            return;
        }

        if (!is_file($json)) {
            $this->log(LogLevel::ERROR, 'Google Indexing API disabled: service account JSON file not found.', ContaoContext::ERROR, [
                'path' => $json,
            ]);
            return;
        }

        try {
            $this->client = new Google_Client();
            $this->client->setAuthConfig($json);
            $this->client->addScope('https://www.googleapis.com/auth/indexing');
        } catch (Throwable $e) {
            $this->client = null;
            $this->log(LogLevel::ERROR, 'Google Indexing API client initialization failed: ' . $e->getMessage(), ContaoContext::ERROR, [
                'path' => $json,
            ]);
        }
    }

    public function indexing(string $page, string $type = self::TYPE_URL_UPDATED): bool
    {
        if (!$this->isClientAvailable()) {
            return false;
        }

        $type = $this->normalizeNotificationType($type);
        $url = $this->toAbsoluteUrl($page);

        if (null === $url) {
            $this->log(LogLevel::ERROR, 'Google Indexing API skipped: URL could not be normalized.', ContaoContext::ERROR, [
                'input_url' => $page,
                'type' => $type,
            ]);
            return false;
        }

        try {
            $httpClient = $this->client->authorize();
            $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
            $response = $httpClient->post($endpoint, [
                'json' => [
                    'url' => $url,
                    'type' => $type,
                ],
            ]);
            $statusText = $response->getReasonPhrase();
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logAnalysis(LogLevel::INFO, 'Google Indexing API request successful.', ContaoContext::CRON, [
                    'status_code' => $statusCode,
                    'status_text' => $statusText,
                    'url' => $url,
                    'type' => $type,
                ]);
                return true;
            } else {
                $this->log(LogLevel::ERROR, 'Google Indexing API request returned non-2xx status.', ContaoContext::ERROR, [
                    'status_code' => $statusCode,
                    'status_text' => $statusText,
                    'url' => $url,
                    'type' => $type,
                ]);
                return false;
            }
        } catch (Throwable $e) {
            $this->log(LogLevel::ERROR, 'Google Indexing API request failed: ' . $e->getMessage(), ContaoContext::ERROR, [
                'url' => $url,
                'type' => $type,
            ]);
            return false;
        }
    }

    /**
     * @see https://github.com/googleapis/google-api-php-client/issues/1567#issuecomment-473228620
     */
    public function batchAll(?int $readerPageId = null): void
    {
        if (!$this->isClientAvailable()) {
            return;
        }

        $readerPage = $this->resolveReaderPage($readerPageId);
        if (null === $readerPage) {
            $this->log(LogLevel::ERROR, 'Google Indexing API batch aborted: no reader page could be resolved.', ContaoContext::ERROR, [
                'requested_reader_page_id' => $readerPageId,
            ]);
            return;
        }

        $baseUrl = $this->resolveBaseUrl($readerPage);
        if (null === $baseUrl) {
            $this->log(LogLevel::ERROR, 'Google Indexing API batch aborted: no valid base URL could be resolved.', ContaoContext::ERROR, [
                'requested_reader_page_id' => $readerPageId,
                'reader_page_id' => (int) $readerPage->id,
            ]);
            return;
        }

        try {
            $this->client->setUseBatch(true);
            $service = new Google_Service_Indexing($this->client);
            $batch = $service->createBatch();
            $count = 0;
            $skippedCount = 0;
            $requestUrls = [];

            if ($jobs = PlakartJobsModel::findBy('published', 1)) {
                while ($jobs->next()) {
                    if ($jobs->current()->isVisible()) {
                        try {
                            $jobUrl = $this->contentUrlGenerator->generate(
                                $readerPage,
                                ['parameters' => '/'.(string) $jobs->alias],
                                UrlGeneratorInterface::ABSOLUTE_PATH
                            );
                            $jobUrl = $this->toAbsoluteUrl($jobUrl, $readerPage, $baseUrl);
                        } catch (Throwable $e) {
                            ++$skippedCount;
                            $this->logAnalysis(LogLevel::WARNING, 'Skipped job in Google batch: URL generation failed.', ContaoContext::ERROR, [
                                'job_id' => $jobs->id,
                                'job_alias' => $jobs->alias,
                                'message' => $e->getMessage(),
                            ]);
                            continue;
                        }

                        if (null === $jobUrl) {
                            ++$skippedCount;
                            $this->logAnalysis(LogLevel::WARNING, 'Skipped job in Google batch: URL could not be normalized.', ContaoContext::ERROR, [
                                'job_id' => $jobs->id,
                                'job_alias' => $jobs->alias,
                            ]);
                            continue;
                        }

                        $postBody = new Google_Service_Indexing_UrlNotification();
                        $postBody->setType(self::TYPE_URL_UPDATED);
                        $postBody->setUrl($jobUrl);
                        $requestKey = \sprintf('job_%s_%d', (string) $jobs->id, $count + 1);
                        $batch->add($service->urlNotifications->publish($postBody), $requestKey);
                        $requestUrls[$requestKey] = $jobUrl;
                        ++$count;
                    }
                }
            }

            if (0 === $count) {
                $this->logAnalysis(LogLevel::INFO, 'Google Indexing API batch skipped: no jobs matched publication criteria.', ContaoContext::CRON);
                return;
            }

            $results = $batch->execute();
            if (!$results) {
                $this->log(LogLevel::ERROR, 'Google Indexing API batch returned no results.', ContaoContext::ERROR, [
                    'notifications' => $count,
                ]);

                return;
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ((array) $results as $requestKey => $result) {
                $requestKey = (string) $requestKey;
                $lookupKey = str_starts_with($requestKey, 'response-') ? substr($requestKey, 9) : $requestKey;

                if ($result instanceof Throwable) {
                    ++$errorCount;
                    $errors[] = [
                        'request_key' => $requestKey,
                        'url' => $requestUrls[$lookupKey] ?? null,
                        'code' => $result->getCode(),
                        'message' => $result->getMessage(),
                    ];
                    continue;
                }

                ++$successCount;
            }

            if ($skippedCount > 0) {
                $this->log(LogLevel::ERROR, 'Google Indexing API batch skipped entries due to URL issues.', ContaoContext::ERROR, [
                    'notifications' => $count,
                    'skipped_count' => $skippedCount,
                ]);
            }

            if ($errorCount > 0) {
                $this->log(LogLevel::ERROR, 'Google Indexing API batch completed with errors.', ContaoContext::ERROR, [
                    'notifications' => $count,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => \array_slice($errors, 0, 10),
                ]);
            } else {
                $this->logAnalysis(LogLevel::INFO, 'Google Indexing API batch successfully sent.', ContaoContext::CRON, [
                    'notifications' => $count,
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                ]);
            }
        } catch (Throwable $e) {
            $this->log(LogLevel::ERROR, 'Google Indexing API batch failed: ' . $e->getMessage(), ContaoContext::ERROR);
        } finally {
            $this->client?->setUseBatch(false);
        }
    }

    private function isClientAvailable(): bool
    {
        $this->initialize();

        if (null !== $this->client) {
            return true;
        }

        $this->log(LogLevel::ERROR, 'Google Indexing API skipped: client is not available.', ContaoContext::ERROR, [
            'service_account_path' => $this->serviceAccountPath,
        ]);

        return false;
    }

    private function normalizeNotificationType(string $type): string
    {
        $normalized = strtoupper(trim($type));

        if (\in_array($normalized, [self::TYPE_URL_UPDATED, self::TYPE_URL_DELETED], true)) {
            return $normalized;
        }

        $this->logAnalysis(LogLevel::WARNING, 'Invalid Google Indexing notification type; falling back to URL_UPDATED.', ContaoContext::ERROR, [
            'provided_type' => $type,
        ]);

        return self::TYPE_URL_UPDATED;
    }

    private function toAbsoluteUrl(string $url, ?PageModel $contextPage = null, ?string $resolvedBaseUrl = null): ?string
    {
        $url = trim($url);

        if ('' === $url) {
            return null;
        }

        if (preg_match('#^https?://#i', $url)) {
            $host = (string) (parse_url($url, \PHP_URL_HOST) ?? '');
            if (!$this->isLocalHost($host)) {
                return $url;
            }

            $baseUrl = $resolvedBaseUrl ?? $this->resolveBaseUrl($contextPage);
            if (null === $baseUrl) {
                return null;
            }

            $path = (string) (parse_url($url, \PHP_URL_PATH) ?? '');
            $query = (string) (parse_url($url, \PHP_URL_QUERY) ?? '');
            $fragment = (string) (parse_url($url, \PHP_URL_FRAGMENT) ?? '');

            $normalized = rtrim($baseUrl, '/');
            if ('' !== $path) {
                $normalized .= '/' . ltrim($path, '/');
            }
            if ('' !== $query) {
                $normalized .= '?' . $query;
            }
            if ('' !== $fragment) {
                $normalized .= '#' . $fragment;
            }

            return $normalized;
        }

        $baseUrl = $resolvedBaseUrl ?? $this->resolveBaseUrl($contextPage);
        if (null === $baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    private function resolveBaseUrl(?PageModel $contextPage = null): ?string
    {
        if (null !== $contextPage) {
            $contextPage = $contextPage->loadDetails();
            $domain = trim((string) ($contextPage->domain ?? ''));
            $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
            $domain = trim($domain, '/');

            if ('' !== $domain && !$this->isLocalHost($domain)) {
                $scheme = !empty($contextPage->rootUseSSL) ? 'https' : 'http';
                return $scheme . '://' . $domain;
            }
        }

        $environmentUrl = trim((string) (Environment::get('url') ?? ''));
        if ('' !== $environmentUrl) {
            $host = (string) (parse_url($environmentUrl, \PHP_URL_HOST) ?? '');
            if (!$this->isLocalHost($host)) {
                return rtrim($environmentUrl, '/');
            }
        }

        $configUrl = trim((string) (Config::get('url') ?? ''));
        if ('' !== $configUrl) {
            $configUrl = preg_match('#^https?://#i', $configUrl) ? $configUrl : 'https://' . ltrim($configUrl, '/');
            $host = (string) (parse_url($configUrl, \PHP_URL_HOST) ?? '');
            if (!$this->isLocalHost($host)) {
                return rtrim($configUrl, '/');
            }
        }

        return null;
    }

    private function isLocalHost(string $host): bool
    {
        $host = strtolower(trim($host));

        if ('' === $host) {
            return true;
        }

        if (\in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        return str_starts_with($host, 'localhost:') || str_starts_with($host, '127.0.0.1:') || str_starts_with($host, '[::1]:');
    }

    private function resolveReaderPage(?int $readerPageId = null): ?PageModel
    {
        if (null !== $readerPageId && $readerPageId > 0) {
            $page = PageModel::findById($readerPageId);
            if (null !== $page) {
                return $page;
            }
        }

        return $this->readerPageResolver->findFirst();
    }

    private function log(string $level, string $message, string $action, array $extra = []): void
    {
        $context = [
            'contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__, $action),
        ];

        if ([] !== $extra) {
            $context += $extra;
        }

        $this->logger->log($level, $message, $context);
    }

    private function logAnalysis(string $level, string $message, string $action, array $extra = []): void
    {
        if (!$this->analysisMode) {
            return;
        }

        $this->log($level, $message, $action, $extra);
    }

    private function isAnalysisMode(): bool
    {
        return (bool) Config::get('gfjApiAnalysis');
    }
}
