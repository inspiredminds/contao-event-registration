<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Image;
use Contao\StringUtil;
use InspiredMinds\ContaoEventRegistration\EventRegistration\LabelBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Callback(table="tl_event_registration", target="list.sorting.child_record")
 */
class ChildRecordCallbackListener
{
    public function __construct(
        private readonly LabelBuilder $labelBuilder,
        private readonly TranslatorInterface $translator,
    ) {
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

            $label = StringUtil::substr($this->flatten($formData), 128);
        }

        $icon = 'visible_.svg';
        $alt = '';
        $attributes = ' style="float:left; margin-right:0.3em;"';

        if ($row['cancelled']) {
            $icon = 'unpublished.svg';
            $alt = $this->translator->trans('tl_event_registration.cancelled.0', [], 'contao_tl_event_registration');
        } elseif ($row['waiting']) {
            $icon = 'news.svg';
            $alt = $this->translator->trans('tl_event_registration.waiting.0', [], 'contao_tl_event_registration');
        } elseif ($row['confirmed']) {
            $icon = 'visible.svg';
            $alt = $this->translator->trans('tl_event_registration.confirmed.0', [], 'contao_tl_event_registration');
        }

        if ($alt) {
            $attributes .= ' title="'.$alt.'"';
        }

        $label = Image::getHtml($icon, '', $attributes).' '.$label;

        return '<div class="tl_content_left">'.$label.'</div>';
    }

    private function flatten(array $data): string
    {
        return implode(', ', array_map(fn ($value) => \is_array($value) ? $this->flatten($value) : $value, $data));
    }
}
