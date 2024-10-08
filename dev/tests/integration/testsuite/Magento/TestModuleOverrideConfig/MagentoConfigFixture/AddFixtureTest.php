<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleOverrideConfig\MagentoConfigFixture;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->objectManager->get(ScopeConfigInterface::class);
    }

    /**
     * Checks that fixture added in global node successfully applied
     *
     * @return void
     */
    public function testGloballyAddFixture(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_4', ScopeInterface::SCOPE_STORES);
        $this->assertEquals('4th field globally overridden value', $value);
    }

    /**
     * Checks that fixture added in test class node successfully applied
     *
     * @return void
     */
    public function testAddFixtureToClass(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_STORES);
        $this->assertEquals('overridden value for full class', $value);
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
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_STORES);
        $this->assertEquals($expectedConfigValue, $value);
    }

    /**
     * @return array
     */
    public static function testDataProvider(): array
    {
        return [
            'first_data_set' => ['expectedConfigValue' => 'overridden value for method'],
            'second_data_set' => ['expectedConfigValue' => 'overridden value for data set']
        ];
    }

    /**
     * Checks that fixtures can be added on website scope
     *
     * @return void
     */
    public function testAddFixtureOnWebsiteScope(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_WEBSITES);
        $this->assertEquals('overridden value for method on website scope', $value);
    }

    /**
     * Checks that fixtures can be added on website scope with specified scope code
     *
     * @return void
     */
    public function testAddFixtureOnWebsiteScopeWithScopeCode(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_WEBSITES, 'base');
        $this->assertEquals('overridden value for base website scope', $value);
    }
}
