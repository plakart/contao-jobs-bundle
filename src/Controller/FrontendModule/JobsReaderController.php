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

namespace Plakart\ContaoJobsBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Plakart\ContaoJobsBundle\Helper\PrepareJobData;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'plakartJobs', template: 'frontend_module/plakart_jobs_reader')]
class JobsReaderController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly ResponseContextAccessor $responseContextAccessor,
        private readonly HtmlDecoder $htmlDecoder,
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $alias = Input::get('auto_item');
        $job = $alias ? PlakartJobsModel::findPublishedByIdOrAlias($alias) : null;

        if (null === $job) {
            // Do not send a 404 in the front end preview, e.g. if the reader page
            // itself is previewed from the back end without a job in the URL.
            if ($this->isPreview()) {
                return new Response();
            }

            throw new PageNotFoundException('Page not found: '.$request->getUri());
        }

        $data = PrepareJobData::forTemplate($job, $model->imgSize, $model->jobsContactImgSize);

        foreach ($data as $key => $value) {
            $template->set($key, $value);
        }

        // Go back link
        $overviewPage = $model->overviewPage ? PageModel::findById($model->overviewPage) : null;
        $template->goBack = $overviewPage ? $this->contentUrlGenerator->generate($overviewPage) : 'javascript:history.back()';

        // Application form
        $template->form = $model->jobsApplicationForm ? Controller::getForm($model->jobsApplicationForm) : false;

        // Content elements
        $text = '';

        foreach (ContentModel::findPublishedByPidAndTable($job->id, 'tl_plakart_jobs') ?? [] as $element) {
            $text .= Controller::getContentElement($element);
        }

        $template->text = $text;

        $this->setMetaData($job);

        // Google for Jobs schema
        $template->addGfjSchema = Config::get('gfjSchema') || Config::get('gfjApi');
        $template->gfj = '';

        if ($template->addGfjSchema) {
            $template->gfj = json_encode(
                PrepareJobData::gfjJobPosting($data, $job->teaser.' '.$text),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_THROW_ON_ERROR,
            );
        }

        $this->tagResponse('contao.db.tl_plakart_jobs.'.$job->id);

        return $template->getResponse();
    }

    private function isPreview(): bool
    {
        return $this->tokenChecker->isPreviewMode() || true === $this->requestStack->getMainRequest()?->attributes->get('_preview');
    }

    private function setMetaData(PlakartJobsModel $job): void
    {
        $responseContext = $this->responseContextAccessor->getResponseContext();

        if (!$responseContext?->has(HtmlHeadBag::class)) {
            return;
        }

        $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
        $htmlHeadBag->setTitle($this->htmlDecoder->inputEncodedToPlainText((string) ($job->pageTitle ?: $job->title)));

        if ($description = (string) ($job->description ?: $job->teaser)) {
            $htmlHeadBag->setMetaDescription($this->htmlDecoder->htmlToPlainText($description));
        }

        if ('' !== trim((string) $job->robots)) {
            $htmlHeadBag->setMetaRobots((string) $job->robots);
        }
    }
}
