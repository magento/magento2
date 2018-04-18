<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

class GuestPaymentMethodManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestPaymentMethodManagementV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    protected function tearDown()
    {
        $this->deleteCart('test_order_1');
        $this->deleteCart('test_order_1_with_payment');
        $this->deleteCart('test_order_with_virtual_product');
        $this->deleteCart('test_order_with_virtual_product_without_address');
        parent::tearDown();
    }

    /**
     * Delete quote by given reserved order ID
     *
     * @param string $reservedOrderId
     * @return void
     */
    protected function deleteCart($reservedOrderId)
    {
        try {
            /** @var $cart \Magento\Quote\Model\Quote */
            $cart = $this->objectManager->get('Magento\Quote\Model\Quote');
            $cart->load($reservedOrderId, 'reserved_order_id');
            if (!$cart->getId()) {
                throw new \InvalidArgumentException('There is no quote with provided reserved order ID.');
            }
            $cart->delete();
            /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
            $quoteIdMask = $this->objectManager->create('Magento\Quote\Model\QuoteIdMask');
            $quoteIdMask->load($cart->getId(), 'quote_id');
            $quoteIdMask->delete();
        } catch (\InvalidArgumentException $e) {
            // Do nothing if cart fixture was not used
        }
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_payment_saved.php
     */
    public function testReSetPayment()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test_order_1_with_payment', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "method" => [
                'method' => 'checkmo',
                'po_number' => null
            ],
        ];

        $this->assertNotNull($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testSetPaymentWithVirtualProduct()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test_order_with_virtual_product', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "method" => [
                'method' => 'checkmo',
                'po_number' => '200'
            ],
        ];
        $this->assertNotNull($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetPaymentWithSimpleProduct()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "method" => [
                'method' => 'checkmo',
                'po_number' => '200'
            ],
        ];

        $this->assertNotNull($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetPaymentWithSimpleProductWithoutAddress()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test_order_with_simple_product_without_address', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'set',
            ],
        ];

        $requestData = [
            "cartId" => $cartId,
            "method" => [
                'method' => 'checkmo',
                'po_number' => '200'
            ],
        ];
        $this->assertNotNull($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/payment-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getList',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $requestResponse = $this->_webApiCall($serviceInfo, $requestData);

        $expectedResponse = [
            'code' => 'checkmo',
            'title' => 'Check / Money order',
        ];

        $this->assertGreaterThan(0, count($requestResponse));
        $this->assertContains($expectedResponse, $requestResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_payment_saved.php
     */
    public function testGet()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1_with_payment', 'reserved_order_id');
        $cartId = $this->getMaskedCartId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $requestResponse = $this->_webApiCall($serviceInfo, $requestData);

        foreach ($this->getPaymentMethodFieldsForAssert() as $field) {
            $this->assertArrayHasKey($field, $requestResponse);
            $this->assertNotNull($requestResponse[$field]);
        }

        $this->assertEquals('checkmo', $requestResponse['method']);
    }

    /**
     * @return array
     */
    protected function getPaymentMethodFieldsForAssert()
    {
        return ['method', 'po_number', 'additional_data'];
    }

    /**
     * Retrieve masked cart ID for guest cart.
     *
     * @param string $cartId
     * @return string
     */
    protected function getMaskedCartId($cartId)
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }
}
