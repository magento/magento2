<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\Inheritance\Fixtures;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestModuleOverrideConfig\Model\FixtureCallStorage;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @var ConfigStorage
     */
    private $configStorage;

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
        $this->configStorage = $this->objectManager->get(ConfigStorage::class);
        $this->fixtureCallStorage = $this->objectManager->get(FixtureCallStorage::class);
    }

    /**
     * @magentoConfigFixture default_store test_section/test_group/field_2 new_value
     * @magentoConfigFixture default_store test_section/test_group/field_3 new_value
     * @magentoApiDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @magentoApiDataFixture Magento/TestModuleOverrideConfig/_files/fixture3_first_module.php
     * @param array $storeConfigs
     * @param array $fixtures
     * @return void
     */
    #[DataProvider('interfaceDataProvider')]
    public function testInterfaceInheritance(
        array $storeConfigs,
        array $fixtures
    ): void {
        $this->assertConfigFieldValues($storeConfigs, ScopeInterface::SCOPE_STORES);
        $this->assertUsedFixturesCount($fixtures);
    }

    /**
     * @magentoConfigFixture default_store test_section/test_group/field_2 new_value
     * @magentoApiDataFixture Magento/TestModuleOverrideConfig/_files/fixture2_first_module.php
     * @param array $storeConfigs
     * @param array $fixtures
     * @return void
     */
    #[DataProvider('abstractDataProvider')]
    public function testAbstractInheritance(
        array $storeConfigs,
        array $fixtures
    ): void {
        $this->assertConfigFieldValues($storeConfigs, ScopeInterface::SCOPE_STORES);
        $this->assertUsedFixturesCount($fixtures);
    }

    /**
     * @return array
     */
    public static function interfaceDataProvider(): array
    {
        return [
            'first_data_set' => [
                [
                    'test_section/test_group/field_1' => [
                        'value' => 'overridden config fixture value for class',
                        'exists_in_db' => true,
                    ],
                    'test_section/test_group/field_2' => [
                        'value' => 'overridden config fixture value for method',
                        'exists_in_db' => true,
                    ],
                    'test_section/test_group/field_3' => [
                        'value' => 'new_value',
                        'exists_in_db' => true,
                    ],
                ],
                [
                    'fixture1_first_module.php' => 1,
                    'fixture2_first_module.php' => 0,
                    'fixture2_second_module.php' => 1,
                    'fixture3_first_module.php' => 1,
                ],
            ],
            'second_data_set' => [
                [
                    'test_section/test_group/field_1' => [
                        'value' => 'overridden config fixture value for class',
                        'exists_in_db' => true,
                    ],
                    'test_section/test_group/field_2' => [
                        'value' => 'overridden config fixture value for method',
                        'exists_in_db' => true,
                    ],
                    'test_section/test_group/field_3' => [
                        'value' => '3rd field website scope default value',
                        'exists_in_db' => false,
                    ],
                ],
                [
                    'fixture1_first_module.php' => 1,
                    'fixture2_first_module.php' => 0,
                    'fixture2_second_module.php' => 1,
                    'fixture3_first_module.php' => 0,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function abstractDataProvider(): array
    {
        return [
            'first_data_set' => [
                [
                    'test_section/test_group/field_1' => [
                        'value' => 'overridden config fixture value for class',
                        'exists_in_db' => true,
                    ],
                    'test_section/test_group/field_2' => [
                        'value' => '2nd field default value',
                        'exists_in_db' => false,
                    ],
                    'test_section/test_group/field_3' => [
                        'value' => 'overridden config fixture value for data set from abstract',
                        'exists_in_db' => true,
                    ],
                ],
                [
                    'fixture1_first_module.php' => 1,
                    'fixture2_first_module.php' => 0,
                    'fixture2_second_module.php' => 0,
                    'fixture3_first_module.php' => 1,
                ],
            ],
            'second_data_set' => [
                [
                    'test_section/test_group/field_1' => [
                        'value' => 'overridden config fixture value for data set from abstract',
                        'exists_in_db' => true,
                    ],
                    'test_section/test_group/field_2' => [
                        'value' => '2nd field default value',
                        'exists_in_db' => false,
                    ],
                    'test_section/test_group/field_3' => [
                        'value' => '3rd field website scope default value',
                        'exists_in_db' => false,
                    ],
                ],
                [
                    'fixture1_first_module.php' => 0,
                    'fixture2_first_module.php' => 0,
                    'fixture1_second_module.php' => 1,
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
        foreach ($configs as $path => $expected) {
            $this->assertEquals($expected['value'], $this->config->getValue($path, $scope, 'default'));
            if ($expected['exists_in_db']) {
                $this->assertEquals(
                    $expected['value'],
                    $this->configStorage->getValueFromDb($path, ScopeInterface::SCOPE_STORES, 'default')
                );
            } else {
                $this->assertFalse(
                    $this->configStorage->checkIsRecordExist($path, ScopeInterface::SCOPE_STORES, 'default')
                );
            }
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
