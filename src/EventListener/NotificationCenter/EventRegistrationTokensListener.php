<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\NotificationCenter;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsForNotificationTypeEvent;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

#[AsEventListener]
class EventRegistrationTokensListener
{
    public function __construct(
        private readonly TokenDefinitionFactoryInterface $factory,
        private readonly array $supportedNotificationTypes,
    ) {
    }

    public function __invoke(GetTokenDefinitionsForNotificationTypeEvent $event): void
    {
        if (!\in_array($event->getNotificationType()->getName(), $this->supportedNotificationTypes, true)) {
            return;
        }

        $event
            ->addTokenDefinition($this->factory->create(AnythingTokenDefinition::class, 'reg_count', 'event_registration.reg_count'))
            ->addTokenDefinition($this->factory->create(TextTokenDefinition::class, 'reg_confirm_url', 'event_registration.reg_confirm_url'))
            ->addTokenDefinition($this->factory->create(TextTokenDefinition::class, 'reg_cancel_url', 'event_registration.reg_cancel_url'))
            ->addTokenDefinition($this->factory->create(AnythingTokenDefinition::class, 'reg_*', 'event_registration.reg_*'))
            ->addTokenDefinition($this->factory->create(AnythingTokenDefinition::class, 'event_*', 'event_registration.event_*'))
        ;
    }
}
