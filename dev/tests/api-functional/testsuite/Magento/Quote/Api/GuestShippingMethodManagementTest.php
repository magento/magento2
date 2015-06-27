<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestShippingMethodManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestShippingMethodManagementV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

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

        $cartId = $this->quote->getId();
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectTotals()->save();
        $requestData = [
            'cartId' => $cartId,
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

        $cartId = $this->quote->getId();
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $requestData = [
            'cartId' => $cartId,
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

        $cartId = $this->quote->getId();
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $requestData = [
            'cartId' => $cartId,
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetMethod()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        $cartId = $quote->getId();
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectTotals()->save();
        list($carrierCode, $methodCode) = explode('_', $shippingAddress->getShippingMethod());
        list($carrierTitle, $methodTitle) = explode(' - ', $shippingAddress->getShippingDescription());
        $data = [
            ShippingMethodInterface::KEY_CARRIER_CODE => $carrierCode,
            ShippingMethodInterface::KEY_METHOD_CODE => $methodCode,
            ShippingMethodInterface::KEY_CARRIER_TITLE => $carrierTitle,
            ShippingMethodInterface::KEY_METHOD_TITLE => $methodTitle,
            ShippingMethodInterface::KEY_SHIPPING_AMOUNT => $shippingAddress->getShippingAmount(),
            ShippingMethodInterface::KEY_BASE_SHIPPING_AMOUNT => $shippingAddress->getBaseShippingAmount(),
            ShippingMethodInterface::KEY_AVAILABLE => true,
            ShippingMethodInterface::KEY_ERROR_MESSAGE => null,
        ];
        $requestData = ["cartId" => $cartId];
        $actual = $this->_webApiCall($this->getSelectedMethodServiceInfo($cartId), $requestData);
        unset($actual[ShippingMethodInterface::KEY_PRICE_EXCL_TAX]);
        unset($actual[ShippingMethodInterface::KEY_PRICE_INCL_TAX ]);
        $this->assertEquals($data, $actual);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testGetMethodOfVirtualCart()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

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

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $result = $this->_webApiCall($this->getSelectedMethodServiceInfo($cartId), ["cartId" => $cartId]);
        $this->assertEquals([], $result);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     */
    public function testGetListForVirtualCart()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

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

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $quote->getShippingAddress()->collectShippingRates();
        $expectedRates = $quote->getShippingAddress()->getGroupedAllShippingRates();

        $expectedData = $this->convertRates($expectedRates, $quote->getQuoteCurrencyCode());

        $requestData = ["cartId" => $cartId];

        $returnedRates = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals($expectedData, $returnedRates);
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
