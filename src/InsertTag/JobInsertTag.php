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

namespace Plakart\ContaoJobsBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\Resolver\InsertTagResolverNestedResolvedInterface;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Plakart\ContaoJobsBundle\Routing\ReaderPageResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Generates the URL of a job detail page:
 *
 *   {{job_url::ID}}            /job/alias
 *   {{job_url::alias}}         /job/alias
 *   {{job_url::ID::absolute}}  https://example.com/job/alias
 */
#[AsInsertTag('job_url')]
class JobInsertTag implements InsertTagResolverNestedResolvedInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly ReaderPageResolver $readerPageResolver,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        $this->framework->initialize();

        $idOrAlias = (string) $insertTag->getParameters()->get(0);
        $arguments = \array_slice($insertTag->getParameters()->all(), 1);

        if ('' === $idOrAlias || !$job = PlakartJobsModel::findByIdOrAlias($idOrAlias)) {
            return new InsertTagResult('', OutputType::url);
        }

        if (!$readerPage = $this->findReaderPage()) {
            return new InsertTagResult('', OutputType::url);
        }

        try {
            $url = $this->urlGenerator->generate(
                $readerPage,
                ['parameters' => '/'.$job->alias],
                \in_array('absolute', $arguments, true) ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH,
            );
        } catch (ExceptionInterface) {
            $url = '';
        }

        return new InsertTagResult($url, OutputType::url);
    }

    /**
     * Prefers a reader page within the current website root, in case there are
     * job listings with different redirect pages.
     */
    private function findReaderPage(): PageModel|null
    {
        $readerPages = $this->readerPageResolver->findAll();
        $currentPage = $this->requestStack->getMainRequest()?->attributes->get('pageModel');

        if ($currentPage instanceof PageModel) {
            foreach ($readerPages as $readerPage) {
                if ((int) $readerPage->loadDetails()->rootId === (int) $currentPage->loadDetails()->rootId) {
                    return $readerPage;
                }
            }
        }

        return [] === $readerPages ? null : reset($readerPages);
    }
}
