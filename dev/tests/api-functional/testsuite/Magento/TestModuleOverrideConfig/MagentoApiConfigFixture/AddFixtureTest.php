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
 * Class checks that magentoConfigFixtures can be added via override config
 *
 * @magentoAppIsolation enabled
 */
class AddFixtureTest extends AbstractOverridesTest
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
     * Checks that fixture added in test class node successfully applied
     *
     * @return void
     */
    public function testAddFixtureToClass(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_STORES, 'default');
        $this->assertEquals('overridden value for full class', $value);
        $this->assertEquals(
            'overridden value for full class',
            $this->configStorage->getValueFromDb(
                'test_section/test_group/field_1',
                ScopeInterface::SCOPE_STORES,
                'default'
            )
        );
    }

    /**
     * Checks that fixtures added in method and data set nodes successfully applied
     *
     * @dataProvider testDataProvider
     *
     * @param string $expectedConfigValue
     * @return void
     */
    public function testAddFixtureToMethod(string $expectedConfigValue): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_STORES, 'default');
        $this->assertEquals($expectedConfigValue, $value);
        $this->assertEquals(
            $expectedConfigValue,
            $this->configStorage->getValueFromDb(
                'test_section/test_group/field_1',
                ScopeInterface::SCOPE_STORES,
                'default'
            )
        );
    }

    /**
     * @return array
     */
    public static function testDataProvider(): array
    {
        return [
            'first_data_set' => ['expected_config_value' => 'overridden value for method'],
            'second_data_set' => ['expected_config_value' => 'overridden value for data set']
        ];
    }

    /**
     * Checks that fixtures can be added on website scope
     *
     * @return void
     */
    public function testAddFixtureOnWebsiteScope(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_WEBSITES, 'base');
        $this->assertEquals('overridden value for method on website scope', $value);
        $this->assertEquals(
            'overridden value for method on website scope',
            $this->configStorage->getValueFromDb(
                'test_section/test_group/field_1',
                ScopeInterface::SCOPE_WEBSITES,
                'base'
            )
        );
    }
}
