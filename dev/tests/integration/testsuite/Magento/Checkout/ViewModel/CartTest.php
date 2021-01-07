<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\ViewModel;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for clear shopping cart config
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = $this->objectManager = Bootstrap::getObjectManager();
        $this->cart = $objectManager->get(Cart::class);
        $this->mutableScopeConfig = $objectManager->get(MutableScopeConfigInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testConfigClearShoppingCartEnabledWithWebsiteScopes()
    {
        // Assert not active by default
        $this->assertFalse($this->cart->isClearShoppingCartEnabled());

        // Enable Clear Shopping Cart in default website scope
        $this->setClearShoppingCartEnabled(
            true,
            ScopeInterface::SCOPE_WEBSITE
        );

        // Assert now active in default website scope
        $this->assertTrue($this->cart->isClearShoppingCartEnabled());

        $defaultStore = $this->storeManager->getStore();
        $defaultWebsite = $defaultStore->getWebsite();
        $defaultWebsiteCode = $defaultWebsite->getCode();

        $secondStore = $this->storeManager->getStore('fixture_second_store');
        $secondWebsite = $secondStore->getWebsite();
        $secondWebsiteCode = $secondWebsite->getCode();

        // Change current store context to that of second website
        $this->storeManager->setCurrentStore($secondStore);

        // Assert not active by default in second website
        $this->assertFalse($this->cart->isClearShoppingCartEnabled());

        // Enable Clear Shopping Cart in second website scope
        $this->setClearShoppingCartEnabled(
            true,
            ScopeInterface::SCOPE_WEBSITE,
            $secondWebsiteCode
        );

        // Assert now active in second website scope
        $this->assertTrue($this->cart->isClearShoppingCartEnabled());

        // Disable Clear Shopping Cart in default website scope
        $this->setClearShoppingCartEnabled(
            false,
            ScopeInterface::SCOPE_WEBSITE,
            $defaultWebsiteCode
        );

        // Assert still active in second website
        $this->assertTrue($this->cart->isClearShoppingCartEnabled());
    }

    /**
     * Set clear shopping cart enabled.
     *
     * @param bool $isActive
     * @param string $scope
     * @param string|null $scopeCode
     */
    private function setClearShoppingCartEnabled(bool $isActive, string $scope, $scopeCode = null)
    {
        $this->mutableScopeConfig->setValue(
            'checkout/cart/enable_clear_shopping_cart',
            $isActive ? '1' : '0',
            $scope,
            $scopeCode
        );
    }
}
