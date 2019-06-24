<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order creation with downloadable product in multi stock mode tests.
 */
class OrderCreateMultiStockModeDownloadableProductTest extends OrderPlacementBase
{
    /**
     * Create order with downloadable product - registered customer, default stock, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530382/scenarios/1820317
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->assignStockToWebsite(1, 'base');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('downloadable-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with downloadable product - registered customer, default stock, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530382/scenarios/2270016
     *
     * @return void
     */
    public function testCustomerPlaceOrderAdditionalWebsiteDefaultStock(): void
    {
        $this->_markTestAsRestOnly();
        $this->setStoreView('store_for_eu_website');
        $this->assignStockToWebsite(1, 'eu_website');
        $this->assignProductsToWebsite(['downloadable-product'], 'eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('downloadable-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
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
        $this->assertEquals('Downloadable Product', $order['items'][0]['name']);
        $this->assertEquals('downloadable', $order['items'][0]['product_type']);
        $this->assertEquals('downloadable-product', $order['items'][0]['sku']);
        $this->assertEquals(10, $order['items'][0]['price']);
    }
}
