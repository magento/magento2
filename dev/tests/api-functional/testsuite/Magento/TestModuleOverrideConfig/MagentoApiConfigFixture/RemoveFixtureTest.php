<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoApiConfigFixture;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestModuleOverrideConfig\AbstractOverridesTest;

/**
 * Class checks that magentoConfigFixtures can be removed using override config
 *
 * @magentoAppIsolation enabled
 */
class RemoveFixtureTest extends AbstractOverridesTest
{
    /** @var ScopeConfigInterface */
    private $config;

    /** @var ConfigStorage */
    private $configStorage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->configStorage = $this->objectManager->get(ConfigStorage::class);
    }

    /**
     * Checks that fixture can be removed in test class node
     *
     * @magentoConfigFixture default_store test_section/test_group/field_1 new_value
     *
     * @return void
     */
    public function testRemoveFixtureForClass(): void
    {
        $value = $this->config->getValue(
            'test_section/test_group/field_1',
            ScopeInterface::SCOPE_STORES,
            'default'
        );
        $this->assertEquals('1st field default value', $value);
        $this->assertFalse(
            $this->configStorage->checkIsRecordExist(
                'test_section/test_group/field_1',
                ScopeInterface::SCOPE_STORES,
                'default'
            )
        );
    }

    /**
     * Checks that fixtures can be removed in method and data set nodes
     *
     * @magentoConfigFixture default_store test_section/test_group/field_2 new_value
     * @magentoConfigFixture default_store test_section/test_group/field_3 new_value
     *
     * @dataProvider testDataProvider
     *
     * @param string $expectedFirstValue
     * @param string $expectedSecondValue
     * @param bool $firstvalueExist
     * @param bool $secondvalueExist
     * @return void
     */
    public function testRemoveFixtureForMethod(
        string $expectedFirstValue,
        string $expectedSecondValue,
        bool $firstvalueExist,
        bool $secondvalueExist
    ): void {
        $fistValue = $this->config->getValue(
            'test_section/test_group/field_2',
            ScopeInterface::SCOPE_STORES,
            'default'
        );
        $secondValue = $this->config->getValue(
            'test_section/test_group/field_3',
            ScopeInterface::SCOPE_STORES,
            'default'
        );
        $this->assertEquals($expectedFirstValue, $fistValue);
        if ($firstvalueExist) {
            $this->assertEquals(
                $expectedFirstValue,
                $this->configStorage->getValueFromDb(
                    'test_section/test_group/field_2',
                    ScopeInterface::SCOPE_STORES,
                    'default'
                )
            );
        }

        $this->assertEquals(
            $firstvalueExist,
            $this->configStorage->checkIsRecordExist(
                'test_section/test_group/field_2',
                ScopeInterface::SCOPE_STORES,
                'default'
            )
        );
        $this->assertEquals($expectedSecondValue, $secondValue);
        if ($secondvalueExist) {
            $this->assertEquals(
                $expectedSecondValue,
                $this->configStorage->getValueFromDb(
                    'test_section/test_group/field_3',
                    ScopeInterface::SCOPE_STORES,
                    'default'
                )
            );
        }
        $this->assertEquals(
            $secondvalueExist,
            $this->configStorage->checkIsRecordExist(
                'test_section/test_group/field_3',
                ScopeInterface::SCOPE_STORES,
                'default'
            )
        );
    }

    /**
     * @return array
     */
    public function testDataProvider(): array
    {
        return [
            'first_data_set' => [
                'expected_first_config_value' => '2nd field default value',
                'expected_second_config_value' => 'new_value',
                'first_value_exist' => false,
                'second_value_exist' => true,
            ],
            'second_data_set' => [
                'expected_first_config_value' => '2nd field default value',
                'expected_second_config_value' => '3rd field website scope default value',
                'first_value_exist' => false,
                'second_value_exist' => false,
            ],
        ];
    }

    /**
     * Checks that website scope fixture can be removed
     *
     * @magentoConfigFixture base_website test_section/test_group/field_3 new_value
     *
     * @return void
     */
    public function testRemoveWebsiteScopeFixture(): void
    {
        $value = $this->config->getValue(
            'test_section/test_group/field_3',
            ScopeInterface::SCOPE_WEBSITES,
            'base'
        );
        $this->assertEquals('3rd field website scope default value', $value);
        $this->assertFalse(
            $this->configStorage->checkIsRecordExist(
                'test_section/test_group/field_3',
                ScopeInterface::SCOPE_WEBSITES,
                'base'
            )
        );
    }
}
