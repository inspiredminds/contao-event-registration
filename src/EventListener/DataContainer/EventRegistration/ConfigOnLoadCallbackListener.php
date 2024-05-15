<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CoreBundle\ServiceAnnotation\Callback;

/**
 * @Callback(table="tl_event_registration", target="config.onload")
 */
class ConfigOnLoadCallbackListener
{
    public function __invoke(): void
    {
        $GLOBALS['TL_CSS'][] = 'bundles/contaoeventregistration/backend.css';
    }
}
