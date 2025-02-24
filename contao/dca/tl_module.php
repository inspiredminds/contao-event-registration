<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Doctrine\DBAL\Platforms\MySQLPlatform;
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

$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo_er'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio'],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['cal_ctemplate_er'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['cal_ctemplate'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => static fn () => Controller::getTemplateGroup('cal_event_registration'),
    'eval' => [
        'includeBlankOption' => true,
        'chosen' => true,
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => 'text',
        'length' => MySQLPlatform::LENGTH_LIMIT_TINYTEXT,
        'notnull' => false,
    ],
];

PaletteManipulator::create()
    ->addField('cal_ctemplate_er', 'cal_ctemplate')
    ->removeField('cal_ctemplate')
    ->addField('jumpTo_er', 'jumpTo')
    ->applyToPalette(EventRegistrationCalendarController::TYPE, 'tl_module')
;
