<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $this->totalsCollector = $this->objectManager->create(\Magento\Quote\Model\Quote\TotalsCollector::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     */
    public function testGetListForVirtualCart()
    {
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        $this->assertEquals([], $this->_webApiCall($this->getListServiceInfo($cartId), ["cartId" => $cartId]));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
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
        $this->_markTestAsRestOnly();

        $this->quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Model\ShippingMethodManagementInterface $shippingMethodManagementService */
        $shippingMethodManagementService = $this->objectManager->create(
            \Magento\Quote\Model\ShippingMethodManagementInterface::class
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
        $converter = $this->objectManager->create(\Magento\Quote\Model\Cart\ShippingMethodConverter::class);
        foreach ($groupedRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $result[] = $converter->modelToDataObject($rate, $currencyCode)->__toArray();
            }
        }
        return $result;
    }
}
