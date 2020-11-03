<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
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

        foreach ($row as $key => $value) {
            unset($formData[$key]);
        }

        $row = array_merge($row, $formData);

        unset($row['form_data']);

        return $row;
    }

    private function decodeFormData(): void
    {
        if (null === $this->decodedFormData) {
            $this->decodedFormData = json_decode($this->form_data ?? '', true) ?: [];
        }
    }
}
