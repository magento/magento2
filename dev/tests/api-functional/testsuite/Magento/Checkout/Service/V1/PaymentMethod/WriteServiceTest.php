<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\PaymentMethod;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class WriteServiceTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'checkoutPaymentMethodWriteServiceV1';
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
        /** @var \Magento\Sales\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_1_with_payment', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
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

        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testSetPaymentWithVirtualProduct()
    {
        /** @var \Magento\Sales\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_with_virtual_product', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
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
        /** @var \Magento\Sales\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
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
        /** @var \Magento\Sales\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_with_virtual_product_without_address', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
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
        $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage Shipping address is not set
     */
    public function testSetPaymentWithSimpleProductWithoutAddress()
    {
        /** @var \Magento\Sales\Model\Quote  $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_with_simple_product_without_address', 'reserved_order_id');
        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-payment-methods',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
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
        $this->_webApiCall($serviceInfo, $requestData);
    }
}
