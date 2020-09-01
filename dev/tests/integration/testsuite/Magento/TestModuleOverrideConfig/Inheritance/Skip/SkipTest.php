<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Inheritance\Skip;

/**
 * Class checks that test method can be skipped using inherited from abstract class/interface override config
 *
 * phpcs:disable Generic.Classes.DuplicateClassName
 *
 * @magentoAppIsolation enabled
 */
class SkipTest extends SkipAbstractClass implements SkipInterface
{
    /**
     * @return void
     */
    public function testAbstractSkip(): void
    {
        $this->fail('This test should be skipped via override config in method node inherited from abstract class');
    }

    /**
     * @return void
     */
    public function testInterfaceSkip(): void
    {
        $this->fail('This test should be skipped via override config in method node inherited from interface');
    }

    /**
     * @dataProvider skipDataProvider
     *
     * @param string $message
     * @return void
     */
    public function testSkipDataSet(string $message): void
    {
        $this->fail($message);
    }

    /**
     * @return array
     */
    public function skipDataProvider(): array
    {
        return [
            'first_data_set' => ['This test should be skipped in data set node inherited from abstract class'],
            'second_data_set' => ['This test should be skipped in data set node inherited from interface'],
        ];
    }
}
