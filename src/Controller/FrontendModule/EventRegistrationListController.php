<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\EventRegistration\LabelBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lists all (confirmed) registrations of an event.
 *
 * @FrontendModule(type=EventRegistrationListController::TYPE, category="events", template="mod_event_registration_list")
 */
class EventRegistrationListController extends AbstractFrontendModuleController
{
    public const TYPE = 'event_registration_list';

    public function __construct(
        private readonly EventRegistration $eventRegistration,
        private readonly LabelBuilder $labelBuilder,
        private readonly Connection $db,
    ) {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $event = $this->eventRegistration->getCurrentEvent();

        if (!$event || !$event->reg_enable) {
            return new Response();
        }

        $mainEvent = $this->eventRegistration->getMainEvent($event);
        $query = 'SELECT * FROM tl_event_registration WHERE pid = ? AND cancelled != 1';

        if ($mainEvent->reg_requireConfirm) {
            $query .= ' AND confirmed = 1';
        }

        $registrations = $this->db->fetchAllAssociative($query, [(int) $mainEvent->id]);

        if ([] === $registrations) {
            return new Response();
        }

        foreach ($registrations as &$registration) {
            if (!isset($registration['label'])) {
                $registration['label'] = ($this->labelBuilder)($registration);
            }

            try {
                $registration['form_data'] = json_decode((string) $registration['form_data'], null, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                // noop
            }

            $registration = (object) $registration;
        }

        $template->event = $event;
        $template->mainEvent = $mainEvent;
        $template->registrations = $registrations;

        return $template->getResponse();
    }
}
