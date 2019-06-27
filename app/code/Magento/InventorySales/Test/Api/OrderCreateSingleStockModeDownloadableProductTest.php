<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order create in single stock mode downloadable product tests.
 */
class OrderCreateSingleStockModeDownloadableProductTest extends OrderPlacementBase
{
    /**
     * Create order with downloadable product - registered customer, single stock mode, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/915538/scenarios/2270523
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('downloadable-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with downloadable product - registered customer, single stock mode, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/915538/scenarios/2270522
     *
     * @return void
     */
    public function testCustomerPlaceOrderAdditionalWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $websiteCode = 'eu_website';
        $this->assignCustomerToCustomWebsite('customer@example.com', $websiteCode);
        $this->assignProductsToWebsite(['downloadable-product'], $websiteCode);
        $this->setStoreView('store_for_eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addProduct('downloadable-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with downloadable product - guest customer, single stock mode, default website.
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @return void
     */
    public function testGuestPlaceOrderWithDefaultWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $this->createCustomerCart();
        $this->addProduct('downloadable-product');
        $this->estimateShippingCosts();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
        $this->cancelOrder($orderId);
    }

    /**
     * Create order with downloadable product - guest customer, single stock mode, additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     *
     * @return void
     */
    public function testGuestPlaceOrderAdditonalWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $websiteCode = 'eu_website';
        $this->assignProductsToWebsite(['downloadable-product'], $websiteCode);
        $this->setStoreView('store_for_eu_website');
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
        $this->assertGreaterThan(0, $order['increment_id']);
        $this->assertEquals('customer@example.com', $order['customer_email']);
        $this->assertEquals('downloadable-product', $order['items'][0]['sku']);
        $this->assertEquals('downloadable', $order['items'][0]['product_type']);
        $this->assertEquals(10, $order['items'][0]['price']);
        $this->assertEquals(1, $order['items'][0]['qty_ordered']);
    }
}
