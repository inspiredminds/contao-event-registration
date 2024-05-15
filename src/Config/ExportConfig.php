<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\Config;

class ExportConfig
{
    public int $pid;

    public string $delimiter = ',';

    public bool $excelCompatible = false;
}
