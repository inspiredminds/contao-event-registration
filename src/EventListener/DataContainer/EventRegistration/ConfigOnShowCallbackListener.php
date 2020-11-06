<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\FormFieldModel;
use Contao\FormModel;

/**
 * @Callback(table="tl_event_registration", target="config.onshow")
 */
class ConfigOnShowCallbackListener
{
    public function __invoke(array $show, array $row, DataContainer $dc): array
    {
        foreach ($show as &$c) {
            foreach ($c as &$table) {
                foreach ($table as $k => $v) {
                    if (false !== strpos($k, 'form_data')) {
                        unset($table[$k]);
                        break;
                    }
                }
            }
        }

        $formData = json_decode($row['form_data'] ?? '', true) ?? [];
        $form = FormModel::findById((int) $row['form']);

        foreach ($formData as $name => $value) {
            $label = '- <small>'.$name.'</small>';

            if (null !== $form && null !== ($formField = FormFieldModel::findBy(['pid = ?', 'name = ?'], [$form->id, $name])) && !empty($formField->label)) {
                $label = $formField->label.' <small>'.$name.'</small>';
            }

            $show['tl_event_registration'][0][$label] = $value;
        }

        return $show;
    }
}
