<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\ContaoEventRegistration\FormField\EventRegistrationFormField;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;

$GLOBALS['TL_MODELS']['tl_event_registration'] = EventRegistrationModel::class;
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_event_registration';
$GLOBALS['TL_FFL']['event_registration'] = EventRegistrationFormField::class;
