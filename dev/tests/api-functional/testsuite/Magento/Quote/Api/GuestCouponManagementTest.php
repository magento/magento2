<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestCouponManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestCouponManagementV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function tearDown()
    {
        $createdQuotes = ['test_order_1', 'test01'];
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        foreach ($createdQuotes as $quoteId) {
            $quote->load($quoteId, 'reserved_order_id');
            $quote->delete();
            /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
            $quoteIdMask = $this->objectManager->create('Magento\Quote\Model\QuoteIdMask');
            $quoteIdMask->load($quoteId, 'quote_id');
            $quoteIdMask->delete();
        }
    }

    protected function getQuoteMaskedId($quoteId)
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create('Magento\Quote\Model\QuoteIdMaskFactory')->create();
        $quoteIdMask->load($quoteId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_coupon_saved.php
     */
    public function testGet()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        $cartId = $this->getQuoteMaskedId($quote->getId());

        $couponCode = $quote->getCouponCode();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons/' ,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($couponCode, $this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_coupon_saved.php
     */
    public function testDelete()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->getQuoteMaskedId($quote->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Remove',
            ],
        ];
        $requestData = ["cartId" => $cartId];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        $quote->load('test_order_1', 'reserved_order_id');
        $this->assertEquals('', $quote->getCouponCode());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Coupon code is not valid
     */
    public function testSetCouponThrowsExceptionIfCouponDoesNotExist()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->getQuoteMaskedId($quote->getId());

        $couponCode = 'invalid_coupon_code';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons/' . $couponCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "couponCode" => $couponCode,
        ];

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     * @magentoApiDataFixture Magento/Checkout/_files/discount_10percent.php
     */
    public function testSetCouponSuccess()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test01', 'reserved_order_id');
        $cartId = $this->getQuoteMaskedId($quote->getId());
        $salesRule = $this->objectManager->create('Magento\SalesRule\Model\Rule');
        $salesRule->load('Test Coupon', 'name');
        $couponCode = $salesRule->getPrimaryCoupon()->getCode();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/coupons/' . $couponCode,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "couponCode" => $couponCode,
        ];

        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));

        $quoteWithCoupon = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quoteWithCoupon->load('test01', 'reserved_order_id');

        $this->assertEquals($quoteWithCoupon->getCouponCode(), $couponCode);
    }
}
