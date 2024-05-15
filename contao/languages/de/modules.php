<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationFormController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationListController;

$GLOBALS['TL_LANG']['FMD'][EventRegistrationFormController::TYPE] = ['Event-Registrierungsformular', 'Modul zur Verarbeitung von Registrierungen zu Events.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationConfirmController::TYPE] = ['Event-Registrierungsbestätigung', 'Modul zur Verarbeitung der Bestätigung einer Registrierung zu einem Event.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationCancelController::TYPE] = ['Event-Registrierungsstornierung', 'Modul zur Verarbeitung der Stornierung einer Registrierung zu einem Event.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationListController::TYPE] = ['Event-Registrierungsliste', 'Zeigt eine Liste aller Registrierungen des aktuellen Events.'];
