<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\CoreBundle\String\SimpleTokenParser;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NodeBundle\NodeManager;
use Terminal42\NotificationCenterBundle\NotificationCenter;

/**
 * @FrontendModule(type=EventRegistrationConfirmController::TYPE, category="events", template="mod_event_registration_confirm")
 */
class EventRegistrationConfirmController extends AbstractFrontendModuleController
{
    public const TYPE = 'event_registration_confirm';

    public const ACTION = 'confirm';

    public function __construct(
        private readonly EventRegistration $eventRegistration,
        private readonly NodeManager $nodeManager,
        private readonly TranslatorInterface $translator,
        private readonly SimpleTokenParser $simpleTokenParser,
        private readonly NotificationCenter $notificationCenter,
    ) {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $uuids = $request->query->all()['uuid'] ?? null;
        $action = $request->query->get('action');

        if (self::ACTION !== $action) {
            return new Response();
        }

        if (empty($uuids)) {
            throw new PageNotFoundException('No UUID given.');
        }

        $registrations = [];
        $template->message = [];

        foreach ((array) $uuids as $uuid) {
            if (!$registration = EventRegistrationModel::findOneByUuid($uuid)) {
                throw new PageNotFoundException('No registration found.');
            }

            $registrations[] = $registration;
            $event = CalendarEventsModel::findById((int) $registration->pid);
            $template->event = $event;
            $template->registration = $registration;

            $this->processConfirm($template, $event, $registration);
        }

        $template->message = implode(' ', array_unique((array) $template->message));

        $tokens = $this->eventRegistration->getSimpleTokensForMultipleRegistrations($registrations);

        $template->content = function () use ($model, $tokens): string|null {
            if ($nodes = StringUtil::deserialize($model->nodes, true)) {
                return $this->simpleTokenParser->parse(implode('', $this->nodeManager->generateMultiple($nodes)), $tokens);
            }

            return null;
        };

        // Send notification
        if ($model->nc_notification) {
            $this->notificationCenter->sendNotification($model->nc_notification, $tokens);
        }

        return $template->getResponse();
    }

    private function processConfirm(Template $template, CalendarEventsModel $event, EventRegistrationModel $registration): void
    {
        // Check if already confirmed
        if ($registration->confirmed) {
            $template->class .= ' already-confirmed';
            $template->alreadyConfirmed = true;
            $template->message = array_merge($template->message, [$this->translator->trans('already_confirmed', [], 'im_contao_event_registration')]);

            return;
        }

        // Check if already cancelled
        if ($registration->cancelled) {
            $template->class .= ' already-cancelled';
            $template->alreadyCancelled = true;
            $template->message = array_merge($template->message, [$this->translator->trans('already_cancelled', [], 'im_contao_event_registration')]);

            return;
        }

        // Check if past registration date
        if (!empty($event->reg_regEnd) && time() > $event->reg_regEnd) {
            $template->class .= ' cannot-confirm';
            $template->cannotConfirm = true;
            $template->message = array_merge($template->message, [$this->translator->trans('cannot_confirm', [], 'im_contao_event_registration')]);

            return;
        }

        $registration->confirmed = true;
        $registration->save();
    }
}
