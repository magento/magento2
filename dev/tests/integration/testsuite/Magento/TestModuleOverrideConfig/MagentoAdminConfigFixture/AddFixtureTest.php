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
 * Class checks that magentoAdminConfigFixtures can be added via override config
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
     * Checks that fixture added in test class node successfully applied
     *
     * @return void
     */
    public function testAddFixtureToClass(): void
    {
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->assertEquals('overridden config fixture value for full class', $value);
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
        $value = $this->config->getValue('test_section/test_group/field_1', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->assertEquals($expectedConfigValue, $value);
    }

    /**
     * @return array
     */
    public function testDataProvider(): array
    {
        return [
            'first_data_set' => ['expected_config_value' => 'overridden config fixture value for method'],
            'second_data_set' => ['expected_config_value' => 'overridden config fixture value for data set']
        ];
    }
}
