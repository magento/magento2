<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Skip;

use Magento\TestModuleOverrideConfig\AbstractOverridesTest;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class checks that only specific data set can be skipped using override config
 *
 * @magentoAppIsolation enabled
 */
class SkipDataSetTest extends AbstractOverridesTest
{
    /**
     * The first_data_set should not be executed according to override config it should be mark as skipped
     * @param $message
     * @return void
     */
    #[DataProvider('configDataProvider')]
    public function testSkipDataSet($message): void
    {
        if ($this->dataName() === 'first_data_set') {
            $this->fail($message);
        }
    }

    /**
     * @return array
     */
    public static function configDataProvider(): array
    {
        return [
            'first_data_set' => ['This test should be skipped via override config in data set node'],
            'second_data_set' => ['This test should be skipped via override config in data set node'],
        ];
    }
}
