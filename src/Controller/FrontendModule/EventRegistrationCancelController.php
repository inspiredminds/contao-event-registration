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
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use NotificationCenter\Model\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NodeBundle\NodeManager;

/**
 * @FrontendModule(type=EventRegistrationCancelController::TYPE, category="events")
 */
class EventRegistrationCancelController extends AbstractFrontendModuleController
{
    public const TYPE = 'event_registration_cancel';
    public const ACTION = 'cancel';

    private $eventRegistration;
    private $nodeManager;
    private $translator;

    public function __construct(EventRegistration $eventRegistration, NodeManager $nodeManager, TranslatorInterface $translator)
    {
        $this->eventRegistration = $eventRegistration;
        $this->nodeManager = $nodeManager;
        $this->translator = $translator;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $uuid = $request->query->get('uuid');
        $action = $request->query->get('action');

        if (self::ACTION !== $action) {
            return new Response();
        }

        if (empty($uuid)) {
            throw new PageNotFoundException('No UUID given.');
        }

        $registration = EventRegistrationModel::findOneByUuid($uuid);

        if (null === $registration) {
            throw new PageNotFoundException('No registration found.');
        }

        $event = CalendarEventsModel::findById((int) $registration->pid);

        $template->event = $event;
        $template->registration = $registration;
        $template->content = function () use ($model): ?string {
            if ($nodes = StringUtil::deserialize($model->nodes, true)) {
                return implode('', $this->nodeManager->generateMultiple($nodes));
            }

            return null;
        };

        $tokens = $this->eventRegistration->getSimpleTokens($event, $registration);

        $this->processCancel($template, $model, $event, $registration, $tokens);

        return new Response(StringUtil::parseSimpleTokens($template->parse(), $tokens));
    }

    private function processCancel(Template $template, ModuleModel $model, CalendarEventsModel $event, EventRegistrationModel $registration, array $tokens): void
    {
        // Check if already cancelled
        if ($registration->cancelled) {
            $template->class .= ' already-cancelled';
            $template->alreadyCancelled = true;
            $template->message = $this->translator->trans('already_cancelled', [], 'im_contao_event_registration');

            return;
        }

        // Check if past cancel date
        if (!empty($event->reg_cancelEnd) && time() > $event->reg_cancelEnd) {
            $template->class .= ' cannot-cancel';
            $template->cannotCancel = true;
            $template->message = $this->translator->trans('cannot_cancel', [], 'im_contao_event_registration');

            return;
        }

        $registration->cancelled = true;
        $registration->save();

        // Send notification
        if (!empty($model->nc_notification) && null !== ($notification = Notification::findByPk((int) $model->nc_notification))) {
            $notification->send($tokens);
        }
    }
}
