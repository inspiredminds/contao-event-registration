<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Config;

class ExportConfig
{
    public int $pid;

    public string $delimiter = ',';

    public bool $excelCompatible = false;
}
