<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;

$GLOBALS['TL_MODELS']['tl_event_registration'] = EventRegistrationModel::class;
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_event_registration';
