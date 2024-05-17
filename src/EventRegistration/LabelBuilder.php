<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventRegistration;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;

/**
 * Builds the label for a registration according to the DCA configuration.
 */
class LabelBuilder
{
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    /**
     * @param EventRegistrationModel|array $row
     */
    public function __invoke($row): string|null
    {
        $this->framework->initialize();

        $table = EventRegistrationModel::getTable();

        Controller::loadDataContainer($table);

        $list = &$GLOBALS['TL_DCA'][$table]['list']['label'] ?? null;
        $label = null;

        if ($row instanceof EventRegistrationModel) {
            $row = $row->row();
        }

        // Build label according to DCA config
        if (null !== $list && !empty($list['fields']) && !empty($list['format'])) {
            $fieldData = [];

            try {
                $formData = json_decode($row['form_data'] ?? '', true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $formData = [];
            }

            foreach ($list['fields'] as $labelField) {
                if (isset($row[$labelField])) {
                    $fieldData[] = $row[$labelField];
                    continue;
                }

                if (isset($formData[$labelField])) {
                    $fieldData[] = $formData[$labelField];
                }
            }

            try {
                $label = vsprintf($list['format'], $fieldData);

                // vsprintf returns false in PHP <8 (see #9)
                if (false === $label) {
                    $label = null;
                }
            } catch (\ValueError) {
                // Ignore value errors
            }
        }

        return $label;
    }
}
