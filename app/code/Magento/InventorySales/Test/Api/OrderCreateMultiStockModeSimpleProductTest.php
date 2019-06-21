<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order creation with simple product in multi stock mode tests.
 */
class OrderCreateMultiStockModeSimpleProductTest extends OrderPlacementBase
{
    /**
     * Create order with simple product - registered customer, default stock, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530370/scenarios/1837241
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('SKU-1');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with simple product - registered customer, default stock, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/assign_products_to_websites.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530370/scenarios/1841699
     *
     * @return void
     */
    public function testCustomerPlaceOrderCustomWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $this->assignStockToWebsite(1, 'eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('SKU-1');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with simple product - registered customer, additional stock, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530370/scenarios/1841119
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsiteCustomStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(10, 'base');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('SKU-1');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with simple product - registered customer, additional stock, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/assign_products_to_websites.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530370/scenarios/1850104
     *
     * @return void
     */
    public function testCustomerPlaceOrderCustomWebsiteCustomStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('SKU-1');
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
        $this->assertEquals('Simple Product 1 Orange', $order['items'][0]['name']);
        $this->assertEquals('simple', $order['items'][0]['product_type']);
        $this->assertEquals('SKU-1', $order['items'][0]['sku']);
        $this->assertEquals(10, $order['items'][0]['price']);
    }
}
