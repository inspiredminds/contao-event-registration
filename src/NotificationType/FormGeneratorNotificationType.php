<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\NotificationType;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

/**
 * This decorates the original Terminal42\NotificationCenterBundle\NotificationType\FormGeneratorNotificationType
 * and adds more tokens to its token definitions.
 */
class FormGeneratorNotificationType implements NotificationTypeInterface
{
    public function __construct(
        private readonly TokenDefinitionFactoryInterface $factory,
        private readonly NotificationTypeInterface $inner,
    ) {
    }

    public function getName(): string
    {
        return $this->inner->getName();
    }

    public function getTokenDefinitions(): array
    {
        return [
            ...$this->inner->getTokenDefinitions(),
            $this->factory->create(AnythingTokenDefinition::class, 'reg_count', 'event_registration.reg_count'),
            $this->factory->create(TextTokenDefinition::class, 'reg_confirm_url', 'event_registration.reg_confirm_url'),
            $this->factory->create(TextTokenDefinition::class, 'reg_cancel_url', 'event_registration.reg_cancel_url'),
            $this->factory->create(AnythingTokenDefinition::class, 'reg_*', 'event_registration.reg_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'event_*', 'event_registration.event_*'),
        ];
    }
}
