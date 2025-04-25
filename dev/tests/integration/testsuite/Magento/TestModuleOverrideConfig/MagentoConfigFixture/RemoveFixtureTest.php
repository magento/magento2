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
 * Class checks that magentoConfigFixtures can be removed using override config
 *
 * @magentoAppIsolation enabled
 */
class RemoveFixtureTest extends AbstractOverridesTest
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
     * Checks that fixture can be removed in test class node
     *
     * @magentoConfigFixture current_store test_section/test_group/field_1 new_value
     *
     * @return void
     */
    public function testRemoveFixtureForClass(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeInterface::SCOPE_STORES);
        $this->assertEquals('1st field default value', $value);
    }

    /**
     * Checks that fixtures can be removed in method and data set nodes
     *
     * @magentoConfigFixture current_store test_section/test_group/field_2 new_value
     * @magentoConfigFixture current_store test_section/test_group/field_3 new_value
     *
     * @dataProvider testDataProvider
     *
     * @param string $expectedFirstValue
     * @param string $expectedSecondValue
     * @return void
     */
    public function testRemoveFixtureForMethod(string $expectedFirstValue, string $expectedSecondValue): void
    {
        $fistValue = $this->config->getValue('test_section/test_group/field_2', ScopeInterface::SCOPE_STORES);
        $secondValue = $this->config->getValue('test_section/test_group/field_3', ScopeInterface::SCOPE_STORES);
        $this->assertEquals($expectedFirstValue, $fistValue);
        $this->assertEquals($expectedSecondValue, $secondValue);
    }

    /**
     * @return array
     */
    public static function testDataProvider(): array
    {
        return [
            'first_data_set' => [
                'expectedFirstValue' => '2nd field default value',
                'expectedSecondValue' => 'new_value',
            ],
            'second_data_set' => [
                'expectedFirstValue' => '2nd field default value',
                'expectedSecondValue' => '3rd field website scope default value',
            ],
        ];
    }

    /**
     * Checks that website scope fixture can be removed
     *
     * @magentoConfigFixture current_website test_section/test_group/field_3 new_value
     *
     * @return void
     */
    public function testRemoveWebsiteScopeFixture(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_3', ScopeInterface::SCOPE_WEBSITES);
        $this->assertEquals('3rd field website scope default value', $value);
    }

    /**
     * Checks that website scope fixture with specified scope code can be removed
     *
     * @magentoConfigFixture base_website test_section/test_group/field_3 new_value
     *
     * @return void
     */
    public function testRemoveWebsiteScopeFixtureWithScopeCode(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_3', ScopeInterface::SCOPE_WEBSITES, 'base');
        $this->assertEquals('3rd field website scope default value', $value);
    }
}
