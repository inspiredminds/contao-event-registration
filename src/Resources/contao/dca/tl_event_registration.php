<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\Controller\EventRegistrationExportController;

$GLOBALS['TL_DCA']['tl_event_registration'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_calendar_events',
        'closed' => true,
        'doNotCopyRecords' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'uuid' => 'index',
            ],
        ],
    ],

    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_calendar_events.title',
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'uuid' => [
            'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
        ],
        'created' => [
            'eval' => ['rgxp' => 'datim'],
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'form' => [
            'foreignKey' => 'tl_form.title',
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'member' => [
            'foreignKey' => 'tl_member.CONCAT(firstname," ",lastname)',
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'amount' => [
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['rgxp' => 'digit', 'minval' => 1, 'tl_class' => 'w50'],
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 1],
        ],
        'confirmed' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'cancelled' => [
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'form_data' => [
            'inputType' => 'textarea',
            'eval' => ['readonly' => true],
            'sql' => ['type' => 'blob', 'notnull' => false],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['id'],
            'headerFields' => ['title'],
            'disableGrouping' => true,
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['firstname', 'lastname', 'email'],
            'format' => '%s %s, %s',
        ],
        'global_operations' => [
            'export' => [
                'label' => ['Export', 'Teilnehmer exportieren.'],
                'route' => EventRegistrationExportController::class,
                'class' => 'event_registration_export',
                'icons' => 'iconCSV.svg',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{reg_legend},form_data,amount,confirmed,cancelled',
    ],
];
