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

namespace Plakart\ContaoJobsBundle\Cron;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Plakart\ContaoJobsBundle\Helper\GoogleApi;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsCronJob('daily')]
class GoogleIndexing
{
    private bool $analysisMode = false;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly GoogleApi $googleApi,
        #[Autowire(service: 'monolog.logger.contao')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $scope): void
    {
        $this->framework->initialize();
        $this->analysisMode = $this->isAnalysisMode();

        if (!Config::get('gfjApi')) {
            $this->log(LogLevel::INFO, 'Daily Google indexing skipped: gfjApi is disabled.', ContaoContext::CRON);
            return;
        }

        try {
            $this->log(
                LogLevel::INFO,
                $this->analysisMode
                    ? sprintf('Starting daily Google indexing batch (scope: %s).', $scope)
                    : 'Starting daily Google indexing batch.',
                ContaoContext::CRON
            );
            $this->googleApi->batchAll();
            $this->log(LogLevel::INFO, 'Finished daily Google indexing batch.', ContaoContext::CRON);
        } catch (Throwable $e) {
            $this->log(LogLevel::ERROR, 'Daily Google indexing batch failed: ' . $e->getMessage(), ContaoContext::ERROR);
        }
    }

    private function isAnalysisMode(): bool
    {
        return (bool) Config::get('gfjApiAnalysis');
    }

    private function log(string $level, string $message, string $action): void
    {
        $this->logger->log($level, $message, [
            'contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__, $action),
        ]);
    }
}
