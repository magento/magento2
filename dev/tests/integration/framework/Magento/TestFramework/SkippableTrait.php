<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework;

use Magento\TestFramework\Workaround\Override\Config;

/**
 * Trait for dynamic skip tests.
 *
 * Any class using this trait is required to implement Magento\TestFramework\SkippableInterface
 */
trait SkippableTrait
{
    /**
     * Checks config and skip test before start.
     *
     * @before
     * @inheritdoc
     */
    public function ___beforeTestRun(): void
    {
        $skipConfig = Config::getInstance()->getSkipConfiguration($this);
        if ($skipConfig['skip']) {
            self::markTestSkipped($skipConfig['skipMessage']);
        }
    }
}
