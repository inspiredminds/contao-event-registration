<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\FrontendModule;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

/**
 * @Callback(table="tl_module", target="fields.reg_confirm_notification.options")
 * @Callback(table="tl_module", target="fields.reg_cancel_notification.options")
 */
class RegistrationStatusNotificationOptionsCallback
{
    public function __invoke(DataContainer $dc): array
    {
        return [];
    }
}
