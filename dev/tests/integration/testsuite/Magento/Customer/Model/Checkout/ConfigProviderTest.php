<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Checkout;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for customer checkout config provider.
 */
class ConfigProviderTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ConfigProvider */
    private $configProviderInterface;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->configProviderInterface = $this->objectManager->get(ConfigProvider::class);
    }

    /**
     * @magentoConfigFixture current_store customer/password/autocomplete_on_storefront 1
     *
     * @return void
     */
    public function testAutocompletePasswordEnabled(): void
    {
        $this->assertEquals('on', $this->configProviderInterface->getConfig()['autocomplete']);
    }

    /**
     * @magentoConfigFixture current_store customer/password/autocomplete_on_storefront 0
     *
     * @return void
     */
    public function testAutocompletePasswordDisabled(): void
    {
        $this->assertEquals('off', $this->configProviderInterface->getConfig()['autocomplete']);
    }
}
