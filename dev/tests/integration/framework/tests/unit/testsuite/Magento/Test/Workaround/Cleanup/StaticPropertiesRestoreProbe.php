<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Cleanup;

use Magento\TestFramework\Workaround\Cleanup\StaticProperties;

/**
 * Counts restoreStaticVariables() calls for endTestSuite tests.
 */
class StaticPropertiesRestoreProbe extends StaticProperties
{
    /**
     * @var int
     */
    public static int $restoreCalls = 0;

    /**
     * @inheritdoc
     */
    public static function restoreStaticVariables()
    {
        self::$restoreCalls++;
    }
}
