<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CoreBundle\ServiceAnnotation\Callback;

/**
 * @Callback(table="tl_event_registration", target="fields.form_data.load")
 */
class FormDataLoadCallbackListener
{
    public function __invoke($value): string
    {
        return json_encode(json_decode($value ?? '') ?: [], JSON_PRETTY_PRINT);
    }
}
