<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCalendarController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationFormController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationListController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][EventRegistrationFormController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventRegistrationConfirmController::TYPE] = '{title_legend},name,headline,type;{config_legend},nodes,nc_notification;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventRegistrationCancelController::TYPE] = '{title_legend},name,headline,type;{config_legend},nodes,nc_notification;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventRegistrationListController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventRegistrationCalendarController::TYPE] = $GLOBALS['TL_DCA']['tl_module']['palettes']['calendar'];

$class = $GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo']['eval']['tl_class'] ?? '';
$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo']['eval']['tl_class'] = trim($class.' clr');

PaletteManipulator::create()
    ->applyToPalette(EventRegistrationCalendarController::TYPE, 'tl_module')
;
