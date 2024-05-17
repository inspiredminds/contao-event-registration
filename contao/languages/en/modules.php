<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationFormController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationListController;

$GLOBALS['TL_LANG']['FMD'][EventRegistrationFormController::TYPE] = ['Event registration form', 'Module handling registration to events.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationConfirmController::TYPE] = ['Event registration confirmation', 'Module handling registration confirmations.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationCancelController::TYPE] = ['Event registration cancellation', 'Module handling registration cancellations.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationListController::TYPE] = ['Event registration list', 'Lists current registrations for an event.'];
