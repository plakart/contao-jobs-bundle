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

namespace Plakart\ContaoJobsBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Message;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_plakart_jobs', target: 'config.onload')]
#[AsCallback(table: 'tl_plakart_jobs_companies', target: 'config.onload')]
#[AsCallback(table: 'tl_plakart_jobs_locations', target: 'config.onload')]
class JobsItemWatcher
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        Message::addInfo($GLOBALS['TL_LANG']['MSC']['plakart_jobs']['notices']['GoogleForJobsLabels'] ?? null);
    }
}