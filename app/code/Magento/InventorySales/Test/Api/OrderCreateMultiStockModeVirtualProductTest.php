<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order creation with virtual product in multi stock mode tests.
 */
class OrderCreateMultiStockModeVirtualProductTest extends OrderPlacementBase
{
    /**
     * Create order with virtual product - registered customer, default stock, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530380/scenarios/1851577
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with virtual product - registered customer, default stock, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530380/scenarios/1851581
     *
     * @return void
     */
    public function testCustomerPlaceOrderAdditionalWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $this->assignStockToWebsite(1, 'eu_website');
        $this->assignProductsToWebsite(['virtual-product'], 'eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with virtual product - registered customer, additional stock, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/product_virtual_source_item_on_additional_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530380/scenarios/1851580
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsiteAdditionalStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(10, 'base');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with virtual product - registered customer, additional stock, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/product_virtual_source_item_on_additional_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530380/scenarios/1851582
     *
     * @return void
     */
    public function testCustomerPlaceOrderAdditionalWebsiteAdditionalStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $this->assignProductsToWebsite(['virtual-product'], 'eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('virtual-product');
        $this->estimateShippingCosts();
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
        $this->assertEquals('Virtual Product', $order['items'][0]['name']);
        $this->assertEquals('virtual', $order['items'][0]['product_type']);
        $this->assertEquals('virtual-product', $order['items'][0]['sku']);
        $this->assertEquals(10, $order['items'][0]['price']);
    }
}
