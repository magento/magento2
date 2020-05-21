<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoDataFixture;

use Magento\TestModuleOverrideConfig\AbstractOverridesTest;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;

/**
 * Checks that magentoDataFixture can be removed using override config
 *
 * @magentoAppIsolation enabled
 */
class RemoveFixtureTest extends AbstractOverridesTest
{
    /** @var FixtureCallStorage */
    private $fixtureCallStorage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureCallStorage = $this->objectManager->get(FixtureCallStorage::class);
    }

    /**
     * Checks that fixture can be removed in test class node
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture1_first_module.php
     *
     * @return void
     */
    public function testRemoveFixtureForClass(): void
    {
        $this->assertEmpty($this->fixtureCallStorage->getFixturesCount('fixture1_first_module.php'));
    }

    /**
     * Checks that fixture can be removed in method and data set nodes
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture3_first_module.php
     *
     * @dataProvider testDataProvider
     *
     * @param string $fixtureName
     * @return void
     */
    public function testRemoveFixtureForMethod(string $fixtureName): void
    {
        $this->assertEmpty($this->fixtureCallStorage->getFixturesCount($fixtureName));
    }

    /**
     * @return array
     */
    public function testDataProvider(): array
    {
        return [
            'first_data_set' => ['fixture2_first_module.php'],
            'second_data_set' => ['fixture3_first_module.php'],
        ];
    }

    /**
     * Checks that same fixtures can be removed few times
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     *
     * @return void
     */
    public function testRemoveSameFixtures(): void
    {
        $this->assertEmpty($this->fixtureCallStorage->getFixturesCount('fixture3_first_module.php'));
    }
}
