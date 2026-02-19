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
use Contao\ContentModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment;
use Contao\PageModel;
use Contao\System;
use Google_Client;
use Google_Service_Indexing;
use Google_Service_Indexing_UrlNotification;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Psr\Log\LogLevel;
use Throwable;

class GoogleApi
{
    private const TYPE_URL_UPDATED = 'URL_UPDATED';
    private const TYPE_URL_DELETED = 'URL_DELETED';

    private ?Google_Client $client = null;
    private ?string $serviceAccountPath = null;

    public function __construct()
    {
        $jsonFileName = trim((string) (Config::get('gfjJsonFileName') ?? ''));
        $kernelProjectDir = System::getContainer()->getParameter('kernel.project_dir');
        $json = $kernelProjectDir . '/var/' . $jsonFileName;
        $this->serviceAccountPath = $json;

        if ('' === $jsonFileName) {
            $this->log(LogLevel::WARNING, 'Google Indexing API disabled: no JSON filename configured.', ContaoContext::ERROR);
            return;
        }

        if (!is_file($json)) {
            $this->log(LogLevel::WARNING, 'Google Indexing API disabled: service account JSON file not found.', ContaoContext::ERROR, [
                'path' => $json,
            ]);
            return;
        }

        try {
            $this->client = new Google_Client();
            $this->client->setAuthConfig($json);
            $this->client->addScope('https://www.googleapis.com/auth/indexing');
            $this->log(LogLevel::INFO, 'Google Indexing API client initialized.', ContaoContext::CRON, [
                'path' => $json,
            ]);
        } catch (Throwable $e) {
            $this->client = null;
            $this->log(LogLevel::ERROR, 'Google Indexing API client initialization failed: ' . $e->getMessage(), ContaoContext::ERROR, [
                'path' => $json,
            ]);
        }
    }

    public function indexing(string $page, string $type = self::TYPE_URL_UPDATED): void
    {
        if (!$this->isClientAvailable()) {
            return;
        }

        $type = $this->normalizeNotificationType($type);
        $url = $this->toAbsoluteUrl($page);

        if (null === $url) {
            $this->log(LogLevel::WARNING, 'Google Indexing API skipped: URL could not be normalized.', ContaoContext::ERROR, [
                'input_url' => $page,
                'type' => $type,
            ]);
            return;
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
                $this->log(LogLevel::INFO, 'Google Indexing API request successful.', ContaoContext::CRON, [
                    'status_code' => $statusCode,
                    'status_text' => $statusText,
                    'url' => $url,
                    'type' => $type,
                ]);
            } else {
                $this->log(LogLevel::WARNING, 'Google Indexing API request returned non-2xx status.', ContaoContext::ERROR, [
                    'status_code' => $statusCode,
                    'status_text' => $statusText,
                    'url' => $url,
                    'type' => $type,
                ]);
            }
        } catch (Throwable $e) {
            $this->log(LogLevel::ERROR, 'Google Indexing API request failed: ' . $e->getMessage(), ContaoContext::ERROR, [
                'url' => $url,
                'type' => $type,
            ]);
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
            $this->log(LogLevel::WARNING, 'Google Indexing API batch aborted: no reader page could be resolved.', ContaoContext::ERROR, [
                'requested_reader_page_id' => $readerPageId,
            ]);
            return;
        }

        try {
            $this->client->setUseBatch(true);
            $service = new Google_Service_Indexing($this->client);
            $batch = $service->createBatch();
            $count = 0;

            if ($jobs = PlakartJobsModel::findBy('published', 1)) {
                $time = time();
                while ($jobs->next()) {
                    if ((int) $jobs->anzeigen_bis >= $time || empty($jobs->anzeigen_bis)) {
                        $jobUrl = PrepareJobData::alias($readerPage, (string) $jobs->alias);
                        $jobUrl = $this->toAbsoluteUrl($jobUrl);

                        if (null === $jobUrl) {
                            $this->log(LogLevel::WARNING, 'Skipped job in Google batch: URL could not be normalized.', ContaoContext::ERROR, [
                                'job_id' => $jobs->id,
                                'job_alias' => $jobs->alias,
                            ]);
                            continue;
                        }

                        $postBody = new Google_Service_Indexing_UrlNotification();
                        $postBody->setType(self::TYPE_URL_UPDATED);
                        $postBody->setUrl($jobUrl);
                        $batch->add($service->urlNotifications->publish($postBody));
                        ++$count;
                    }
                }
            }

            if (0 === $count) {
                $this->log(LogLevel::INFO, 'Google Indexing API batch skipped: no jobs matched publication criteria.', ContaoContext::CRON);
                return;
            }

            $results = $batch->execute();
            if ($results) {
                $this->log(LogLevel::INFO, 'Google Indexing API batch successfully sent.', ContaoContext::CRON, [
                    'notifications' => $count,
                ]);
            } else {
                $this->log(LogLevel::WARNING, 'Google Indexing API batch returned no results.', ContaoContext::ERROR, [
                    'notifications' => $count,
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
        if (null !== $this->client) {
            return true;
        }

        $this->log(LogLevel::WARNING, 'Google Indexing API skipped: client is not available.', ContaoContext::ERROR, [
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

        $this->log(LogLevel::WARNING, 'Invalid Google Indexing notification type; falling back to URL_UPDATED.', ContaoContext::ERROR, [
            'provided_type' => $type,
        ]);

        return self::TYPE_URL_UPDATED;
    }

    private function toAbsoluteUrl(string $url): ?string
    {
        $url = trim($url);

        if ('' === $url) {
            return null;
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $baseUrl = trim((string) (Config::get('url') ?? ''));
        if ('' === $baseUrl) {
            $baseUrl = trim((string) (Environment::get('url') ?? ''));
        }

        if ('' === $baseUrl) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    private function resolveReaderPage(?int $readerPageId = null): ?PageModel
    {
        if (null !== $readerPageId && $readerPageId > 0) {
            $page = PageModel::findById($readerPageId);
            if (null !== $page) {
                return $page;
            }
        }

        $listings = ContentModel::findBy(
            ['type=?', 'jumpTo>?'],
            ['jobs_listing', 0],
            ['order' => 'id ASC']
        );

        if (null === $listings) {
            return null;
        }

        while ($listings->next()) {
            $page = PageModel::findById((int) $listings->jumpTo);
            if (null !== $page) {
                return $page;
            }
        }

        return null;
    }

    private function log(string $level, string $message, string $action, array $extra = []): void
    {
        $context = [
            'contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__, $action),
        ];

        if ([] !== $extra) {
            $context += $extra;
        }

        System::getContainer()->get('monolog.logger.contao')->log($level, $message, $context);
    }
}
