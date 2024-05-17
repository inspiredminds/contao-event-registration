<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

Controller::loadDataContainer('tl_content');

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_enable'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => ['submitOnChange' => true],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_form'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'chosen' => true, 'submitOnChange' => true, 'tl_class' => 'w50 wizard'],
    'wizard' => [
        ['tl_content', 'editForm'],
    ],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_min'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => ['rgxp' => 'digit', 'maxlength' => 255, 'tl_class' => 'clr w50'],
    'sql' => ['type' => 'integer', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_max'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => ['rgxp' => 'digit', 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql' => ['type' => 'integer', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_regEnd'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_cancelEnd'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['reg_requireConfirm'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['__selector__'][] = 'reg_enable';
$GLOBALS['TL_DCA']['tl_calendar_events']['subpalettes']['reg_enable'] = 'reg_form,reg_min,reg_max,reg_regEnd,reg_cancelEnd,reg_requireConfirm';

foreach ($GLOBALS['TL_DCA']['tl_calendar_events']['palettes'] as $name => $palette) {
    if (!is_string($palette)) {
        continue;
    }

    PaletteManipulator::create()
        ->addLegend('reg_legend', null, PaletteManipulator::POSITION_AFTER, true)
        ->addField('reg_enable', 'reg_legend')
        ->applyToPalette($name, 'tl_calendar_events')
    ;
}

$GLOBALS['TL_DCA']['tl_calendar_events']['config']['ctable'][] = 'tl_event_registration';

$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations'] = array_slice($GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations'], 0, 6, true) + [
    'registrations' => [
        'href' => 'table=tl_event_registration',
        'icon' => 'mgroup.svg',
    ],
] + array_slice($GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations'], 6, count($GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']) - 1, true);
