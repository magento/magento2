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
 * Class check that magentoDataFixtures can be replaced using override config
 *
 * @magentoAppIsolation enabled
 */
class ReplaceFixtureTest extends AbstractOverridesTest
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
     * Checks that fixture can be replaced in test class node
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture1_first_module.php
     *
     * @return void
     */
    public function testReplaceFixtureForClass(): void
    {
        $this->assertEquals(0, $this->fixtureCallStorage->getFixturesCount('fixture1_first_module.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture1_second_module.php'));
    }

    /**
     * Checks that fixture can be replaced in method and data set nodes
     *
     * @dataProvider replacedFixturesProvider
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture1_first_module.php
     *
     * @param string $fixture
     * @return void
     */
    public function testReplaceFixturesForMethod(string $fixture): void
    {
        $this->assertEquals(0, $this->fixtureCallStorage->getFixturesCount('fixture1_first_module.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount($fixture));
    }

    /**
     * @return array
     */
    public function replacedFixturesProvider(): array
    {
        return [
            'first_data_set' => [
                'fixture2_second_module.php',
            ],
            'second_data_set' => [
                'fixture3_second_module.php',
            ],
        ];
    }

    /**
     * Checks that replace config from last loaded file will be applied
     *
     * @dataProvider dataProvider
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture1_first_module.php
     *
     * @param string $fixture
     * @return void
     */
    public function testReplaceFixtureViaThirdModule(string $fixture): void
    {
        $this->assertEquals(0, $this->fixtureCallStorage->getFixturesCount('fixture1_first_module.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount($fixture));
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'first_data_set' => [
                'fixture2_second_module.php',
            ],
            'second_data_set' => [
                'fixture3_second_module.php',
            ],
        ];
    }

    /**
     * Checks that fixture required in the another fixture can be replaced using override
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig2/_files/fixture_with_required_fixture.php
     *
     * @return void
     */
    public function testReplaceRequiredFixture(): void
    {
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture_with_required_fixture.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture2_second_module.php'));
        $this->assertEmpty($this->fixtureCallStorage->getFixturesCount('fixture3_second_module.php'));
    }

    /**
     * Checks that fixture required in the another fixture will be replaced according to last loaded override
     *
     * @magentoDataFixture Magento/TestModuleOverrideConfig2/_files/fixture_with_required_fixture.php
     *
     * @return void
     */
    public function testReplaceRequiredFixtureViaThirdModule(): void
    {
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture_with_required_fixture.php'));
        $this->assertEquals(1, $this->fixtureCallStorage->getFixturesCount('fixture1_third_module.php'));
        $this->assertEmpty($this->fixtureCallStorage->getFixturesCount('fixture2_second_module.php'));
        $this->assertEmpty($this->fixtureCallStorage->getFixturesCount('fixture3_second_module.php'));
    }
}
