<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoAdminConfigFixture;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestModuleOverrideConfig\AbstractOverridesTest;

/**
 * Class check that fixtures can be replaced using override config
 *
 * @magentoAppIsolation enabled
 */
class ReplaceFixtureTest extends AbstractOverridesTest
{
    /** @var ScopeConfigInterface */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
    }

    /**
     * Checks that fixture can be replaced in test class node
     *
     * @magentoAdminConfigFixture test_section/test_group/field_1 new_value
     *
     * @return void
     */
    public function testReplaceFixtureForClass(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->assertEquals('Overridden admin config fixture for class', $value);
    }

    /**
     * Checks that fixture can be replaced in method and data set nodes
     *
     * @magentoAdminConfigFixture test_section/test_group/field_1 new_value
     *
     * @dataProvider testDataProvider
     *
     * @param string $expectedConfigValue
     * @return void
     */
    public function testReplaceFixtureForMethod(string $expectedConfigValue): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->assertEquals($expectedConfigValue, $value);
    }

    /**
     * @return array
     */
    public function testDataProvider(): array
    {
        return [
            'first_data_set' => [
                'expected_config_value' => 'Overridden admin config fixture for method',
            ],
            'second_data_set' => [
                'expected_config_value' => 'Overridden admin config fixture for data set',
            ],
        ];
    }

    /**
     * Checks that replace config from last loaded file will be applied
     *
     * @magentoAdminConfigFixture test_section/test_group/field_1 new_value
     *
     * @dataProvider configValuesDataProvider
     *
     * @param string $expectedConfigValue
     * @return void
     */
    public function testReplaceFixtureViaThirdModule(string $expectedConfigValue): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->assertEquals($expectedConfigValue, $value);
    }

    /**
     * @return array
     */
    public function configValuesDataProvider(): array
    {
        return [
            'first_data_set' => [
                'expected_config_value' => 'Overridden admin config fixture for method from third module',
            ],
            'second_data_set' => [
                'expected_config_value' => 'Overridden admin config fixture for data set from third module',
            ],
        ];
    }
}
