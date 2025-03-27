<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(type=EventRegistrationFormController::TYPE, category="events", template="mod_event_registration_form")
 */
class EventRegistrationFormController extends AbstractFrontendModuleController
{
    public const TYPE = 'event_registration_form';

    public function __construct(private readonly EventRegistration $eventRegistration)
    {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $event = $this->eventRegistration->getCurrentEvent();

        if (!$event || !$event->reg_enable) {
            return new Response();
        }

        $this->eventRegistration->addTemplateData($template, $event);

        return $template->getResponse();
    }
}
