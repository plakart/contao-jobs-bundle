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
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Plakart\ContaoJobsBundle\Helper\GoogleApi;
use Psr\Log\LogLevel;
use Throwable;

#[AsCronJob('daily')]
class GoogleIndexing
{
    public function __invoke(): void
    {
        if (!Config::get('gfjApi')) {
            $this->log(LogLevel::INFO, 'Daily Google indexing skipped: gfjApi is disabled.', ContaoContext::CRON);
            return;
        }

        try {
            $this->log(LogLevel::INFO, 'Starting daily Google indexing batch.', ContaoContext::CRON);
            (new GoogleApi())->batchAll();
            $this->log(LogLevel::INFO, 'Finished daily Google indexing batch.', ContaoContext::CRON);
        } catch (Throwable $e) {
            $this->log(LogLevel::ERROR, 'Daily Google indexing batch failed: ' . $e->getMessage(), ContaoContext::ERROR);
        }
    }

    private function log(string $level, string $message, string $action): void
    {
        System::getContainer()
            ->get('monolog.logger.contao')
            ->log($level, $message, [
                'contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__, $action),
            ]);
    }
}
