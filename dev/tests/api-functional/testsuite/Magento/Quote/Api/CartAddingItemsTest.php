<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class for payment info in quote for registered customer.
 */
class CartAddingItemsTest extends WebapiAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test price for cart after adding product to.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_without_options_with_stock_data.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_one_address.php
     * @return void
     */
    public function testPriceForCreatingQuoteFromEmptyCart()
    {
        $this->_markTestAsRestOnly();

        // Get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken(
            'customer_one_address@test.com',
            'password'
        );

        // Creating empty cart for registered customer.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];

        $quoteId = $this->_webApiCall($serviceInfo, ['customerId' => 999]); // customerId 999 will get overridden
        $this->assertGreaterThan(0, $quoteId);

        // Adding item to the cart
        $serviceInfoForAddingProduct = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/items',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $requestData = [
            'cartItem' => [
                'quote_id' => $quoteId,
                'sku' => 'simple',
                'qty' => 1
            ]
        ];
        $item = $this->_webApiCall($serviceInfoForAddingProduct, $requestData);
        $this->assertNotEmpty($item);
        $this->assertEquals(10, $item['price']);

        // Get payment information
        $serviceInfoForGettingPaymentInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/payment-information',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];
        $paymentInfo = $this->_webApiCall($serviceInfoForGettingPaymentInfo);
        $this->assertEquals($paymentInfo['totals']['grand_total'], 10);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load($quoteId);
        $quote->delete();
    }
}
