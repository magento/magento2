<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order creation with grouped product in multi stock mode tests.
 */
class OrderCreateMultiStockModeGroupedProductTest extends OrderPlacementBase
{
    /**
     * Create order with grouped product - registered customer, default stock, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530373/scenarios/1870535
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('grouped_in_stock');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with grouped product - registered customer, default stock, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530373/scenarios/1870535
     *
     * @return void
     */
    public function testCustomerPlaceOrderCustomWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $this->assignStockToWebsite(1, 'eu_website');
        $this->assignProductsToWebsite(['grouped_in_stock', 'simple_22', 'simple_11'], 'eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('grouped_in_stock');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Verify created order is correct.
     *
     * @param int $orderId
     * @return void
     */
    private function verifyCreatedOrder(int $orderId): void
    {
        $order = $this->getOrder($orderId);
        $this->assertEquals('customer@example.com', $order['customer_email']);
        $this->assertEquals('Simple 11', $order['items'][0]['name']);
        $this->assertEquals('grouped', $order['items'][0]['product_type']);
        $this->assertEquals('simple_11', $order['items'][0]['sku']);
        $this->assertEquals(100, $order['items'][0]['price']);
        $this->assertEquals('Simple 22', $order['items'][1]['name']);
        $this->assertEquals('grouped', $order['items'][1]['product_type']);
        $this->assertEquals('simple_22', $order['items'][1]['sku']);
        $this->assertEquals(100, $order['items'][1]['price']);
    }
}
