<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCalendarController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationFormController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationListController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\MemberEventRegistrationsController;

$GLOBALS['TL_LANG']['FMD'][EventRegistrationFormController::TYPE] = ['Event registration form', 'Module handling registration to events.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationConfirmController::TYPE] = ['Event registration confirmation', 'Module handling registration confirmations.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationCancelController::TYPE] = ['Event registration cancellation', 'Module handling registration cancellations.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationListController::TYPE] = ['Event registration list', 'Lists current registrations for an event.'];
$GLOBALS['TL_LANG']['FMD'][EventRegistrationCalendarController::TYPE] = ['Event registration calendar', 'Provides a calendar where one can select multiple events for registration.'];
$GLOBALS['TL_LANG']['FMD'][MemberEventRegistrationsController::TYPE] = ['Event registrations', 'Lists current registrations for a member.'];
