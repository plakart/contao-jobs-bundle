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

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Environment;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Plakart\ContaoJobsBundle\Helper\PrepareJobData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;

use Contao\Input;
use Contao\ContentModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Contao\Config;

#[AsFrontendModule(category: 'plakartJobs', template: 'plakart_jobs_reader')]
class JobsReaderController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $alias = Input::get('auto_item');
        if($alias && $job = PlakartJobsModel::findByIdOrAlias($alias)) {

            // image
            if($job->singleSRC) {
                $job->figure = PrepareJobData::image($job->singleSRC, $model->imgSize, $job->fullsize);
            }

            // categories
            if($job->categories) {
                $job->categories = PrepareJobData::categories($job->categories);
            }

            // employmentType
            if($job->employmentType) {
                $job->employmentType = PrepareJobData::employmentType($job->employmentType);
            }

            // go back link
            $template->goBack = 'javascript:history.back()';
            if($model->overviewPage) {
                $template->goBack = System::getContainer()->get('contao.routing.content_url_generator')->generate(PageModel::findById($model->overviewPage));
            }

            // form
            $template->form = false;
            if($model->jobsApplicationForm) {
                $template->form = Controller::getForm($model->jobsApplicationForm);
            }

            // meta/seo
            $title = (string) ($job->pageTitle ?: $job->title ?: '');
            $description = (string) ($job->description ?: $job->teaser ?: '');
            $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();
            if ($responseContext?->has(HtmlHeadBag::class))
            {
                $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
                $htmlDecoder = System::getContainer()->get('contao.string.html_decoder');

                $htmlHeadBag->setTitle($htmlDecoder->inputEncodedToPlainText($title));
                $htmlHeadBag->setMetaDescription($htmlDecoder->inputEncodedToPlainText($description));

                if ('' !== trim((string) $job->robots)) {
                    $htmlHeadBag->setMetaRobots((string) $job->robots);
                }
            }

            // all other values
            foreach($job->row() as $k => $v) {
                $template->{$k} = $v;
            }

            // get the elements
            if($content = ContentModel::findPublishedByPidAndTable($job->id, 'tl_plakart_jobs')) {
                $strContent = '';
                while($content->next()) {
                    $strContent .= Controller::getContentElement($content->id);
                }
                $template->text = $strContent;
            }

            // google for jobs schema
            $gfj = [
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',
                'title' => $job->title,
                'description' => PrepareJobData::gfjDescription($strContent ?? ''),
                'datePosted' => date('Y-m-d', $job->date),
                'employmentType' => implode(',',$job->employmentType),
                'hiringOrganization' => [
                    '@type' => 'Organization',
                    'name' => PrepareJobData::gfjCompanyName(),
                    'sameAs' => PrepareJobData::gfjCompanyUrl(),
                    'logo' => PrepareJobData::gfjCompanyLogo()
                ],
                'jobLocation' => [
                    '@type' => 'Place',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => PrepareJobData::gfjCompanyStreetAddress($job->street,$job->streetNr),
                        'addressLocality' => PrepareJobData::gfjCompanyCity($job->city),
                        'postalCode' => PrepareJobData::gfjCompanyZip($job->zip),
                    ]
                ]
            ];

            // optional gfj parameters
            if($job->employmentValidThrough) $gfj['validThrough'] = date('d-m-Y', (int) $job->employmentValidThrough);
            if($job->employmentSalary) $gfj['baseSalary'] = $job->employmentSalary . ' ' . $job->employmentSalaryPer;

            $template->addGfjSchema = Config::get('gfjSchema') ||  Config::get('gfjApi') ?? false;
            $template->gfj = json_encode($gfj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

        } else {
            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        return $template->getResponse();
    }
}
