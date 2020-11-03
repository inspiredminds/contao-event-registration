<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Config;

class ExportConfig
{
    /** @var int */
    public $pid;

    /** @var string */
    public $delimiter = ',';

    /** @var bool */
    public $excelCompatible = false;
}
