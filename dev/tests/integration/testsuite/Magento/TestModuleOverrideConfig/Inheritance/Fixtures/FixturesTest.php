<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Inheritance\Fixtures;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;

/**
 * Class checks that fixtures override config inherited from abstract class and interface.
 *
 * phpcs:disable Generic.Classes.DuplicateClassName
 *
 * @magentoAppIsolation enabled
 */
class FixturesTest extends FixturesAbstractClass implements FixturesInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var FixtureCallStorage
     */
    private $fixtureCallStorage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->fixtureCallStorage = $this->objectManager->get(FixtureCallStorage::class);
    }

    /**
     * @magentoAdminConfigFixture test_section/test_group/field_2 new_value
     * @magentoAdminConfigFixture test_section/test_group/field_3 new_value
     * @magentoConfigFixture current_store test_section/test_group/field_2 new_value
     * @magentoConfigFixture current_store test_section/test_group/field_3 new_value
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture3_first_module.php
     * @magentoDataFixtureBeforeTransaction Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoDataFixtureBeforeTransaction Magento/TestModuleOverrideConfig/_files/fixture3_first_module.php
     * @dataProvider interfaceDataProvider
     * @param array $configs
     * @param array $storeConfigs
     * @param array $fixtures
     * @return void
     */
    public function testInterfaceInheritance(
        array $configs,
        array $storeConfigs,
        array $fixtures
    ): void {
        $this->assertConfigFieldValues($configs);
        $this->assertConfigFieldValues($storeConfigs, ScopeInterface::SCOPE_STORES);
        $this->assertUsedFixturesCount($fixtures);
    }

    /**
     * @magentoAdminConfigFixture test_section/test_group/field_2 new_value
     * @magentoConfigFixture current_store test_section/test_group/field_2 new_value
     * @magentoDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoDataFixtureBeforeTransaction Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @dataProvider abstractDataProvider
     * @param array $configs
     * @param array $storeConfigs
     * @param array $fixtures
     * @return void
     */
    public function testAbstractInheritance(
        array $configs,
        array $storeConfigs,
        array $fixtures
    ): void {
        $this->assertConfigFieldValues($configs);
        $this->assertConfigFieldValues($storeConfigs, ScopeInterface::SCOPE_STORES);
        $this->assertUsedFixturesCount($fixtures);
    }

    /**
     * @return array
     */
    public function interfaceDataProvider(): array
    {
        return [
            'first_data_set' => [
                'admin_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for class',
                    'test_section/test_group/field_2' => 'overridden config fixture value for method',
                    'test_section/test_group/field_3' => 'new_value',
                ],
                'store_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for class',
                    'test_section/test_group/field_2' => 'overridden config fixture value for method',
                    'test_section/test_group/field_3' => 'new_value',
                ],
                'fixtures' => [
                    'fixture1_first_module.php' => 2,
                    'fixture2_first_module.php' => 0,
                    'fixture2_second_module.php' => 2,
                    'fixture3_first_module.php' => 2,
                ],
            ],
            'second_data_set' => [
                'admin_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for class',
                    'test_section/test_group/field_2' => 'overridden config fixture value for method',
                    'test_section/test_group/field_3' => '3rd field default value',
                ],
                'store_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for class',
                    'test_section/test_group/field_2' => 'overridden config fixture value for method',
                    'test_section/test_group/field_3' => '3rd field website scope default value',
                ],
                'fixtures' => [
                    'fixture1_first_module.php' => 2,
                    'fixture2_first_module.php' => 0,
                    'fixture2_second_module.php' => 2,
                    'fixture3_first_module.php' => 0,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function abstractDataProvider(): array
    {
        return [
            'first_data_set' => [
                'admin_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for class',
                    'test_section/test_group/field_2' => '2nd field default value',
                    'test_section/test_group/field_3' => 'overridden config fixture value for data set from abstract',
                ],
                'store_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for class',
                    'test_section/test_group/field_2' => '2nd field default value',
                    'test_section/test_group/field_3' => 'overridden config fixture value for data set from abstract',
                ],
                'fixtures' => [
                    'fixture1_first_module.php' => 2,
                    'fixture2_first_module.php' => 0,
                    'fixture2_second_module.php' => 0,
                    'fixture3_first_module.php' => 2,
                ],
            ],
            'second_data_set' => [
                'admin_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for data set from abstract',
                    'test_section/test_group/field_2' => '2nd field default value',
                    'test_section/test_group/field_3' => '3rd field default value',
                ],
                'store_configs' => [
                    'test_section/test_group/field_1' => 'overridden config fixture value for data set from abstract',
                    'test_section/test_group/field_2' => '2nd field default value',
                    'test_section/test_group/field_3' => '3rd field website scope default value',
                ],
                'fixtures' => [
                    'fixture1_first_module.php' => 0,
                    'fixture2_first_module.php' => 0,
                    'fixture1_second_module.php' => 2,
                    'fixture3_first_module.php' => 0,
                ],
            ],
        ];
    }

    /**
     * Asserts config field values.
     *
     * @param array $configs
     * @param string $scope
     * @return void
     */
    private function assertConfigFieldValues(
        array $configs,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): void {
        foreach ($configs as $path => $expectedValue) {
            $this->assertEquals($expectedValue, $this->config->getValue($path, $scope));
        }
    }

    /**
     * Asserts count of used fixtures.
     *
     * @param array $fixtures
     * @return void
     */
    private function assertUsedFixturesCount(array $fixtures): void
    {
        foreach ($fixtures as $fixture => $count) {
            $this->assertEquals($count, $this->fixtureCallStorage->getFixturesCount($fixture));
        }
    }
}
