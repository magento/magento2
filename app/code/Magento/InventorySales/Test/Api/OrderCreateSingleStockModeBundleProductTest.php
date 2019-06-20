<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Api;

/**
 * Web Api order create in single stock mode bundle product tests.
 */
class OrderCreateSingleStockModeBundleProductTest extends OrderPlacementBase
{
    /**
     * Create order with bundle product - registered customer, single stock mode, default website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/915538/scenarios/1820315
     *
     * @return void
     */
    public function testCustomerPlaceOrderDefaultWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addBundleProduct('bundle-product');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with bundle product - registered customer, single stock mode, additional website.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/915538/scenarios/1820316
     *
     * @return void
     */
    public function testCustomerPlaceOrderAdditionalWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $websiteCode = 'eu_website';
        $products = ['bundle-product', 'simple'];
        $this->assignCustomerToCustomWebsite('customer@example.com', $websiteCode);
        $this->assignProductsToWebsite($products, $websiteCode);
        $this->setStoreView('store_for_eu_website');
        $this->getCustomerToken('customer@example.com', 'password');
        $this->createCustomerCart();
        $this->addBundleProduct('bundle-product');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with bundle product - guest customer, single stock mode, default website.
     *
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     *
     * @return void
     */
    public function testGuestPlaceOrderDefaultWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $this->createCustomerCart();
        $this->addBundleProduct('bundle-product');
        $this->estimateShippingCosts();
        $this->setShippingAndBillingInformation();
        $orderId = $this->submitPaymentInformation();
        $this->verifyCreatedOrder($orderId);
    }

    /**
     * Create order with bundle product - guest customer, single stock mode, additional website.
     *
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     *
     * @return void
     */
    public function testGuestPlaceOrderAdditonalWebsite(): void
    {
        $this->_markTestAsRestOnly();
        $websiteCode = 'eu_website';
        $products = ['simple', 'bundle-product'];
        $this->assignProductsToWebsite($products, $websiteCode);
        $this->setStoreView('store_for_eu_website');
        $this->createCustomerCart();
        $this->addBundleProduct('bundle-product');
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
        $this->assertGreaterThan(0, $order['increment_id']);
        $this->assertEquals('customer@example.com', $order['customer_email']);
        $this->assertEquals('bundle-product', $order['items'][0]['sku']);
        $this->assertEquals('Bundle Product', $order['items'][0]['name']);
        $this->assertEquals('bundle', $order['items'][0]['product_type']);
        $this->assertEquals(15.5, $order['items'][0]['price']);
        $this->assertEquals(1, $order['items'][0]['qty_ordered']);
        $this->assertEquals('simple', $order['items'][1]['sku']);
        $this->assertEquals('Simple Product', $order['items'][1]['name']);
        $this->assertEquals('simple', $order['items'][1]['product_type']);
        $this->assertEquals(0, $order['items'][1]['price']);
        $this->assertEquals(2, $order['items'][1]['qty_ordered']);
    }
}
