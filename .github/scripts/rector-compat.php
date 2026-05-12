<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/**
 * Rector configuration compatible with both Rector 1.x and 2.x.
 *
 * The upstream magento-coding-standard rector.php uses singleton() which
 * does not register rules in Rector 2.x. This wrapper uses rule() instead.
 */
declare(strict_types=1);

use Magento2\Rector\Src\ReplaceMbStrposNullLimit;
use Magento2\Rector\Src\ReplaceNewDateTimeNull;
use Magento2\Rector\Src\ReplacePregSplitNullLimit;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector;
use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Php80\Rector\ClassMethod\SetStateToStaticRector;

return static function (RectorConfig $rectorConfig): void {
    $envVersion = getenv('RECTOR_PHP_VERSION') ?: '8.3';
    $constantName = 'PHP_' . str_replace('.', '', $envVersion);
    $phpVersion = defined(PhpVersion::class . '::' . $constantName)
        ? constant(PhpVersion::class . '::' . $constantName)
        : PhpVersion::PHP_83;
    $rectorConfig->phpVersion($phpVersion);

    $rules = [
        FinalPrivateToPrivateVisibilityRector::class,
        OptionalParametersAfterRequiredRector::class,
        SetStateToStaticRector::class,
        StringableForToStringRector::class,
        // Php81ResourceReturnToObjectRector was removed in Rector 2.x
        ReplacePregSplitNullLimit::class,
        ReplaceMbStrposNullLimit::class,
        ReplaceNewDateTimeNull::class,
    ];

    // rule() works in both Rector 1.x and 2.x
    foreach ($rules as $rule) {
        $rectorConfig->rule($rule);
    }
};
