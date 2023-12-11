<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Skip;

use Magento\TestModuleOverrideConfig\AbstractOverridesTest;

/**
 * Class checks that only specific data set can be skipped using override config
 *
 * @magentoAppIsolation enabled
 */
class SkipDataSetTest extends AbstractOverridesTest
{
    /**
     * The first_data_set should not be executed according to override config it should be mark as skipped
     *
     * @dataProvider testDataProvider
     *
     * @return void
     */
    public function testSkipDataSet(): void
    {
        if ($this->dataName() === 'first_data_set') {
            $this->fail('This test should be skipped via override config in data set node');
        }
    }

    /**
     * @return array
     */
    public function testDataProvider(): array
    {
        return [
            'first_data_set' => [],
            'second_data_set' => [],
        ];
    }
}
