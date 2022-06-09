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
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param EventRegistrationModel|array $row
     */
    public function __invoke($row): ?string
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
            $formData = json_decode($row['form_data'] ?? '', true) ?: [];

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
            } catch (\ValueError $e) {
                // Ignore value errors
            }
        }

        return $label;
    }
}
