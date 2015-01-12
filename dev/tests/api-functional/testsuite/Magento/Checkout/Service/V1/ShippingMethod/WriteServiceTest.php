<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\ShippingMethod;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class WriteServiceTest extends WebapiAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Model\Quote
     */
    protected $quote;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create('Magento\Sales\Model\Quote');
    }

    protected function getServiceInfo()
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $this->quote->getId() . '/selected-shipping-method',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'checkoutShippingMethodWriteServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'checkoutShippingMethodWriteServiceV1SetMethod',
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetMethod()
    {
        $this->quote->load('test_order_1', 'reserved_order_id');
        $serviceInfo = $this->getServiceInfo();

        $requestData = [
            'cartId' => $this->quote->getId(),
            'carrierCode' => 'flatrate',
            'methodCode' => 'flatrate',
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(true, $result);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetMethodWrongMethod()
    {
        $this->quote->load('test_order_1', 'reserved_order_id');
        $serviceInfo = $this->getServiceInfo();

        $requestData = [
            'cartId' => $this->quote->getId(),
            'carrierCode' => 'flatrate',
            'methodCode' => 'wrongMethod',
        ];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\SoapFault $e) {
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $message = json_decode($e->getMessage())->message;
        }
        $this->assertEquals('Carrier with such method not found: flatrate, wrongMethod', $message);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetMethodWithoutShippingAddress()
    {
        $this->quote->load('test_order_with_simple_product_without_address', 'reserved_order_id');
        $serviceInfo = $this->getServiceInfo();

        $requestData = [
            'cartId' => $this->quote->getId(),
            'carrierCode' => 'flatrate',
            'methodCode' => 'flatrate',
        ];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\SoapFault $e) {
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $message = json_decode($e->getMessage())->message;
        }
        $this->assertEquals('Shipping address is not set', $message);
    }
}
