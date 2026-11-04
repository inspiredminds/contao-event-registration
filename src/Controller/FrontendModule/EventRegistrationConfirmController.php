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
use InspiredMinds\ContaoEventRegistration\WaitingListChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
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
        private readonly LockFactory $lockFactory,
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
        $confirmed = false;

        foreach ((array) $uuids as $uuid) {
            if (!$registration = EventRegistrationModel::findOneByUuid($uuid)) {
                throw new PageNotFoundException('No registration found.');
            }

            $registrations[] = $registration;
            $event = CalendarEventsModel::findById((int) $registration->pid);
            $template->event = $event;
            $template->registration = $registration;

            if ($this->processConfirm($template, $event, $registration)) {
                $confirmed = true;
            }
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
        if ($confirmed && $model->nc_notification) {
            $this->notificationCenter->sendNotification($model->nc_notification, $tokens);
        }

        return $template->getResponse();
    }

    private function processConfirm(Template $template, CalendarEventsModel $event, EventRegistrationModel $registration): bool
    {
        // The confirmation decides whether the registration gets a slot or is
        // put on the waiting list. It therefore shares the lock with new
        // registrations and the waiting list processing. The registration is
        // reloaded after acquiring the lock, so concurrent confirmation
        // requests cannot confirm the same registration twice.
        $lock = $this->lockFactory->createLock(WaitingListChecker::getLockName());
        $lock->acquire(true);

        try {
            if (!$registration = EventRegistrationModel::findByPk((int) $registration->id)) {
                return false;
            }

            // A confirmed registration is final for the DOI flow.
            if ($registration->confirmed) {
                $template->class .= ' already-confirmed';
                $template->alreadyConfirmed = true;
                $template->message = [...$template->message, $this->translator->trans('already_confirmed', [], 'im_contao_event_registration')];

                return false;
            }

            if ($registration->cancelled) {
                $template->class .= ' already-cancelled';
                $template->alreadyCancelled = true;
                $template->message = [...$template->message, $this->translator->trans('already_cancelled', [], 'im_contao_event_registration')];

                return false;
            }

            if (!empty($event->reg_regEnd) && time() > $event->reg_regEnd) {
                $template->class .= ' cannot-confirm';
                $template->cannotConfirm = true;
                $template->message = [...$template->message, $this->translator->trans('cannot_confirm', [], 'im_contao_event_registration')];

                return false;
            }

            $waiting = $this->eventRegistration->isRegistrationOnWaitingList($event, (int) $registration->amount);

            if ($waiting && !$event->reg_enableWaitingList) {
                $template->class .= ' cannot-confirm';
                $template->cannotConfirm = true;
                $template->message = [...$template->message, $this->translator->trans('cannot_confirm', [], 'im_contao_event_registration')];

                return false;
            }

            $now = time();

            $registration->confirmed = true;
            $registration->waiting = $waiting;
            $registration->confirmed_at = $now;
            $registration->tstamp = $now;
            $registration->save();

            return true;
        } finally {
            $lock->release();
        }
    }
}
