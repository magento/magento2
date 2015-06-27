<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Api\Data\ShippingMethodInterface;

class ShippingMethodManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteShippingMethodManagementV1';
    const RESOURCE_PATH = '/V1/carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create('Magento\Quote\Model\Quote');
    }

    protected function getServiceInfo()
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/carts/' . $this->quote->getId() . '/selected-shipping-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Set',
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
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectTotals()->save();
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
        $expectedMessage = 'Carrier with such method not found: %1, %2';
        $this->quote->load('test_order_1', 'reserved_order_id');
        $serviceInfo = $this->getServiceInfo();
        $carrierCode = 'flatrate';
        $methodCode = 'wrongMethod';

        $requestData = [
            'cartId' => $this->quote->getId(),
            'carrierCode' => $carrierCode,
            'methodCode' => $methodCode,
        ];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals([$carrierCode, $methodCode], $errorObj['parameters']);
        }
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

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetMethodForMyCart()
    {
        $this->_markTestAsRestOnly();

        $this->quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/selected-shipping-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
                'token' => $token
            ]
        ];

        $requestData = [
            'cartId' => 999,
            'carrierCode' => 'flatrate',
            'methodCode' => 'flatrate',
        ]; // cartId 999 will be overridden
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectTotals()->save();
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(true, $result);

        /** @var \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagementService */
        $shippingMethodManagementService = $this->objectManager->create(
            'Magento\Quote\Api\ShippingMethodManagementInterface'
        );
        $shippingMethod = $shippingMethodManagementService->get($this->quote->getId());

        $this->assertNotNull($shippingMethod);
        $this->assertEquals('flatrate', $shippingMethod->getCarrierCode());
        $this->assertEquals('flatrate', $shippingMethod->getMethodCode());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetMethod()
    {
        $this->quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagementService */
        $shippingMethodManagementService = $this->objectManager->create(
            'Magento\Quote\Api\ShippingMethodManagementInterface'
        );

        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectTotals()->save();
        list($carrierCode, $methodCode) = explode('_', $shippingAddress->getShippingMethod());
        list($carrierTitle, $methodTitle) = explode(' - ', $shippingAddress->getShippingDescription());
        $shippingMethod = $shippingMethodManagementService->get($this->quote->getId());

        $data = [
            ShippingMethodInterface::KEY_CARRIER_CODE => $carrierCode,
            ShippingMethodInterface::KEY_METHOD_CODE => $methodCode,
            ShippingMethodInterface::KEY_CARRIER_TITLE => $carrierTitle,
            ShippingMethodInterface::KEY_METHOD_TITLE => $methodTitle,
            ShippingMethodInterface::KEY_BASE_SHIPPING_AMOUNT => $shippingAddress->getBaseShippingAmount(),
            ShippingMethodInterface::KEY_SHIPPING_AMOUNT => $shippingAddress->getShippingAmount(),
            ShippingMethodInterface::KEY_AVAILABLE => true,
            ShippingMethodInterface::KEY_ERROR_MESSAGE => null,
            ShippingMethodInterface::KEY_PRICE_EXCL_TAX => $shippingMethod->getPriceExclTax(),
            ShippingMethodInterface::KEY_PRICE_INCL_TAX => $shippingMethod->getPriceInclTax(),
        ];

        $requestData = ["cartId" => $this->quote->getId()];
        $this->assertEquals(
            $data,
            $this->_webApiCall($this->getSelectedMethodServiceInfo($this->quote->getId()), $requestData)
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testGetMethodOfVirtualCart()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        $result = $this->_webApiCall($this->getSelectedMethodServiceInfo($cartId), ["cartId" => $cartId]);
        $this->assertEquals([], $result);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetMethodOfCartWithNoShippingMethod()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $cartId = $quote->load('test_order_1', 'reserved_order_id')->getId();

        $result = $this->_webApiCall($this->getSelectedMethodServiceInfo($cartId), ["cartId" => $cartId]);
        $this->assertEquals([], $result);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture  Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetMethodForMyCart()
    {
        $this->_markTestAsRestOnly();

        $this->quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagementService */
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectTotals()->save();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/selected-shipping-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, []);
        $this->assertEquals('flatrate', $result[ShippingMethodInterface::KEY_CARRIER_CODE]);
        $this->assertEquals('flatrate', $result[ShippingMethodInterface::KEY_METHOD_CODE]);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     */
    public function testGetListForVirtualCart()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        $this->assertEquals([], $this->_webApiCall($this->getListServiceInfo($cartId), ["cartId" => $cartId]));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        $quote->getShippingAddress()->collectShippingRates();
        $expectedRates = $quote->getShippingAddress()->getGroupedAllShippingRates();

        $expectedData = $this->convertRates($expectedRates, $quote->getQuoteCurrencyCode());

        $requestData = ["cartId" => $cartId];

        $returnedRates = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals($expectedData, $returnedRates);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetListForMyCart()
    {
        $this->markTestSkipped('Will be fixed after MAGETWO-35573');
        $this->_markTestAsRestOnly();

        $this->quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagementService */
        $shippingMethodManagementService = $this->objectManager->create(
            'Magento\Quote\Api\ShippingMethodManagementInterface'
        );
        $shippingMethodManagementService->set($this->quote->getId(), 'flatrate', 'flatrate');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/shipping-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, []);
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);

        $shippingMethod = $shippingMethodManagementService->get($this->quote->getId());
        $expectedData = [
            ShippingMethodInterface::KEY_CARRIER_CODE => $shippingMethod->getCarrierCode(),
            ShippingMethodInterface::KEY_METHOD_CODE => $shippingMethod->getMethodCode(),
            ShippingMethodInterface::KEY_CARRIER_TITLE => $shippingMethod->getCarrierTitle(),
            ShippingMethodInterface::KEY_METHOD_TITLE => $shippingMethod->getMethodTitle(),
            ShippingMethodInterface::KEY_SHIPPING_AMOUNT => $shippingMethod->getAmount(),
            ShippingMethodInterface::KEY_BASE_SHIPPING_AMOUNT => $shippingMethod->getBaseAmount(),
            ShippingMethodInterface::KEY_AVAILABLE => $shippingMethod->getAvailable(),
            ShippingMethodInterface::KEY_ERROR_MESSAGE => null,
            ShippingMethodInterface::KEY_PRICE_EXCL_TAX => $shippingMethod->getPriceExclTax(),
            ShippingMethodInterface::KEY_PRICE_INCL_TAX => $shippingMethod->getPriceInclTax(),
        ];

        $this->assertEquals($expectedData, $result[0]);
    }

    /**
     * @param string $cartId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getSelectedMethodServiceInfo($cartId)
    {
        return $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/selected-shipping-method',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
    }

    /**
     * Service info
     *
     * @param int $cartId
     * @return array
     */
    protected function getListServiceInfo($cartId)
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/shipping-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
    }

    /**
     * Convert rate models array to data array
     *
     * @param string $currencyCode
     * @param \Magento\Quote\Model\Quote\Address\Rate[] $groupedRates
     * @return array
     */
    protected function convertRates($groupedRates, $currencyCode)
    {
        $result = [];
        /** @var \Magento\Quote\Model\Cart\ShippingMethodConverter $converter */
        $converter = $this->objectManager->create('\Magento\Quote\Model\Cart\ShippingMethodConverter');
        foreach ($groupedRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $result[] = $converter->modelToDataObject($rate, $currencyCode)->__toArray();
            }
        }
        return $result;
    }
}
