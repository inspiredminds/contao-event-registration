<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\NotificationCenter;

use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\NotificationTypes;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Event\GetNotificationTypeForModuleConfigEvent;

#[AsEventListener]
class GetNotificationTypeForModuleConfigEventListener
{
    public function __invoke(GetNotificationTypeForModuleConfigEvent $event): void
    {
        if ('nc_notification' !== $event->getField()) {
            return;
        }

        switch ($event->getModuleConfig()->getType()) {
            case EventRegistrationConfirmController::TYPE: $event->setNotificationType(NotificationTypes::CONFIRM);
                break;
            case EventRegistrationCancelController::TYPE: $event->setNotificationType(NotificationTypes::CANCEL);
                break;
        }
    }
}
