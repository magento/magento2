<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Skip;

use Magento\TestModuleOverrideConfig\AbstractOverridesTest;

/**
 * Class checks that full test class can be skipped
 *
 * @magentoAppIsolation enabled
 */
class SkipClassTest extends AbstractOverridesTest
{
    /**
     * This test should not be executed according to override config it should be mark as skipped
     *
     * @return void
     */
    public function testClassSkip(): void
    {
        $this->fail('This test should be skipped via override config in test class node');
    }
}
