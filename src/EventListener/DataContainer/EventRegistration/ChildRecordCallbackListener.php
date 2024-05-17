<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Image;
use Contao\StringUtil;
use InspiredMinds\ContaoEventRegistration\EventRegistration\LabelBuilder;

/**
 * @Callback(table="tl_event_registration", target="list.sorting.child_record")
 */
class ChildRecordCallbackListener
{
    public function __construct(private readonly LabelBuilder $labelBuilder)
    {
    }

    public function __invoke(array $row): string
    {
        $label = ($this->labelBuilder)($row);

        // Show all form data values as a fallback
        if (null === $label || '' === $label || '0' === $label) {
            try {
                $formData = json_decode($row['form_data'] ?? '', true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $formData = [];
            }

            $label = StringUtil::substr(implode(', ', array_filter($formData)), 128);
        }

        $icon = 'visible_.svg';

        if ($row['cancelled']) {
            $icon = 'unpublished.svg';
        } elseif ($row['confirmed']) {
            $icon = 'visible.svg';
        }

        $label = Image::getHtml($icon, '', ' style="float:left; margin-right:0.3em;"').' '.$label;

        return '<div class="tl_content_left">'.$label.'</div>';
    }
}
