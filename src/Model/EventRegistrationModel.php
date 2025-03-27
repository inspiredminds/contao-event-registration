<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $uuid
 * @property int    $created
 * @property int    $form
 * @property int    $member
 * @property int    $amount
 * @property bool   $confirmed
 * @property bool   $cancelled
 * @property string $form_data
 */
class EventRegistrationModel extends Model
{
    protected static $strTable = 'tl_event_registration';

    protected $decodedFormData;

    /**
     * @param string $key
     */
    public function __get($key)
    {
        if (\array_key_exists($key, $this->arrData)) {
            return parent::__get($key);
        }

        $this->decodeFormData();

        return $this->decodedFormData[$key] ?? null;
    }

    /**
     * Returns an associative array with the regular database record and the form data combined.
     */
    public function getCombinedRow(): array
    {
        $row = $this->row();

        $this->decodeFormData();

        $formData = $this->decodedFormData;

        foreach (array_keys($row) as $key) {
            unset($formData[$key]);
        }

        $row = array_merge($row, $formData);

        unset($row['form_data']);

        return $row;
    }

    private function decodeFormData(): void
    {
        if (null === $this->decodedFormData) {
            try {
                $this->decodedFormData = json_decode($this->form_data ?? '', true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $this->decodedFormData = [];
            }
        }
    }
}
