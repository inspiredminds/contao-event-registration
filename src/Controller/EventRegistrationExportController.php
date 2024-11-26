<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Controller;

use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Message;
use Contao\System;
use InspiredMinds\ContaoBackendFormsBundle\Form\BackendForm;
use InspiredMinds\ContaoEventRegistration\Config\ExportConfig;
use InspiredMinds\ContaoEventRegistration\Exception\ExportException;
use InspiredMinds\ContaoEventRegistration\Export\EventRegistrationExport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsController]
class EventRegistrationExportController extends AbstractBackendController implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EventRegistrationExport $exporter,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $eventId): Response
    {
        $event = CalendarEventsModel::findById($eventId);

        if (null === $event) {
            throw new PageNotFoundException('The given event does not exist.');
        }

        /** @var AttributeBagInterface $backendSession */
        $backendSession = $request->getSession()->getBag('contao_backend');

        // Get the form
        $form = $this->buildForm($request, $backendSession);

        // Check the form
        if ($form->validate()) {
            $config = new ExportConfig();
            $config->pid = $eventId;
            $config->delimiter = $form->fetch('delimiter');
            $config->excelCompatible = (bool) $form->fetch('excelCompatible');

            $backendSession->set('event_registration_export_delimiter', $config->delimiter);
            $backendSession->set('event_registration_export_excel', $config->excelCompatible);

            try {
                $csv = $this->exporter->getCsv($config);

                return new Response(
                    $csv->toString(),
                    200,
                    [
                        'Content-Encoding' => 'none',
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="'.$event->alias.'.csv"',
                        'Content-Description' => 'File Transfer',
                    ],
                );
            } catch (ExportException $e) {
                Message::addError($e->getMessage(), 'BE');
            }
        }

        return $this->render('@ContaoEventRegistration/be_event_registration_export.html.twig', [
            'headline' => $this->translator->trans('event_registration_export', ['event' => $event->title], 'im_contao_event_registration'),
            'backUrl' => System::getReferer(),
            'form' => $form->generate(),
            'messages' => Message::generate(),
        ]);
    }

    private function buildForm(Request $request, AttributeBagInterface $backendSession): BackendForm
    {
        $form = new BackendForm('exportConfig', Request::METHOD_POST, static fn ($form) => $request->request->get('FORM_SUBMIT') === $form->getFormId());

        $form->setLegend($this->translator->trans('export', [], 'im_contao_event_registration'));

        $form->addFormField('delimiter', [
            'label' => ['Delimiter', 'Delimiter for the CSV.'],
            'inputType' => 'radio',
            'options' => [
                ',' => $this->translator->trans('delimiter_comma', [], 'im_contao_event_registration'),
                ';' => $this->translator->trans('delimiter_semi', [], 'im_contao_event_registration'),
            ],
            'eval' => ['tl_class' => ''],
            'default' => $backendSession->get('event_registration_export_delimiter') ?? ',',
        ]);

        $form->addFormField('excelCompatible', [
            'label' => ['Microsoft Excel compatibility', 'Creates a Microsoft Excel compatible CSV.'],
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'default' => $backendSession->get('event_registration_export_excel') ?? false,
        ]);

        if (class_exists(CodefogHasteBundle::class)) {
            $form->addSubmitFormField($this->translator->trans('export', [], 'im_contao_event_registration'));
        } else {
            $form->addSubmitFormField('submit', $this->translator->trans('export', [], 'im_contao_event_registration'));
        }

        return $form;
    }
}
