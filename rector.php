<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__.'/vendor/contao/rector/config/contao.php']);

    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/contao',
    ]);

    $rectorConfig->skip([FirstClassCallableRector::class]);

    $rectorConfig->parallel();
    $rectorConfig->cacheDirectory(sys_get_temp_dir().'/rector_cache');
};
