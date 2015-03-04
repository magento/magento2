<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

class PaymentMethodManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quotePaymentMethodManagementV1';
    const RESOURCE_PATH = '/V1/carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_payment_saved.php
     */
    public function testReSetPayment()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test_order_1_with_payment', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
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
                'po_number' => null,
                'cc_owner' => 'John',
                'cc_type' => null,
                'cc_exp_year' => null,
                'cc_exp_month' => null,
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
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
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
                'po_number' => '200',
                'cc_owner' => 'tester',
                'cc_type' => 'test',
                'cc_exp_year' => '2014',
                'cc_exp_month' => '1',
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
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
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
                'po_number' => '200',
                'cc_owner' => 'tester',
                'cc_type' => 'test',
                'cc_exp_year' => '2014',
                'cc_exp_month' => '1',
            ],
        ];

        $this->assertNotNull($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Billing address is not set
     */
    public function testSetPaymentWithVirtualProductWithoutAddress()
    {
        /** @var \Magento\Quote\Model\Quote  $quote */
        $quote = $this->objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test_order_with_virtual_product_without_address', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
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
                'po_number' => '200',
                'cc_owner' => 'tester',
                'cc_type' => 'test',
                'cc_exp_year' => '2014',
                'cc_exp_month' => '1',
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
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
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
                'po_number' => '200',
                'cc_owner' => 'tester',
                'cc_type' => 'test',
                'cc_exp_year' => '2014',
                'cc_exp_month' => '1',
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
        $cartId = $quote->getId();

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
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
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

        $this->assertArrayHasKey('method', $requestResponse);
        $this->assertArrayHasKey('po_number', $requestResponse);
        $this->assertArrayHasKey('cc_owner', $requestResponse);
        $this->assertArrayHasKey('cc_type', $requestResponse);
        $this->assertArrayHasKey('cc_exp_year', $requestResponse);
        $this->assertArrayHasKey('cc_exp_month', $requestResponse);
        $this->assertArrayHasKey('additional_data', $requestResponse);

        $this->assertNotNull($requestResponse['method']);
        $this->assertNotNull($requestResponse['po_number']);
        $this->assertNotNull($requestResponse['cc_owner']);
        $this->assertNotNull($requestResponse['cc_type']);
        $this->assertNotNull($requestResponse['cc_exp_year']);
        $this->assertNotNull($requestResponse['cc_exp_month']);
        $this->assertNotNull($requestResponse['additional_data']);

        $this->assertEquals('checkmo', $requestResponse['method']);
    }
}
