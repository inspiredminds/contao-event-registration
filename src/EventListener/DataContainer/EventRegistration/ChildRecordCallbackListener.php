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
use Contao\Image;
use Contao\StringUtil;

/**
 * @Callback(table="tl_event_registration", target="list.sorting.child_record")
 */
class ChildRecordCallbackListener
{
    public function __invoke(array $row): string
    {
        $list = &$GLOBALS['TL_DCA']['tl_event_registration']['list']['label'] ?? null;
        $record = null;

        // Build record according to DCA config
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
                $record = vsprintf($list['format'], $fieldData);
            } catch (\ValueError $e) {
                // Ignore value errors
            }
        }

        // Show all form data values as a fallback
        if (empty($record)) {
            $record = StringUtil::substr(implode(', ', array_filter($formData)), 128);
        }

        $icon = 'visible_.svg';

        if ($row['cancelled']) {
            $icon = 'unpublished.svg';
        } elseif ($row['confirmed']) {
            $icon = 'visible.svg';
        }

        $record = Image::getHtml($icon, '', ' style="float:left; margin-right:0.3em;"').' '.$record;

        return '<div class="tl_content_left">'.$record.'</div>';
    }
}
