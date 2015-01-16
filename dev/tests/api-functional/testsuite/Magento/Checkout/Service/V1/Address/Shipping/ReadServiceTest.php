<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Address\Shipping;

use Magento\Checkout\Service\V1\Data\Cart\Address;
use Magento\Checkout\Service\V1\Data\Cart\Address\Region;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class ReadServiceTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'checkoutAddressShippingReadServiceV1';
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetAddress()
    {
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Sales\Model\Quote\Address  $address */
        $address = $quote->getShippingAddress();

        $data = [
            Address::KEY_COUNTRY_ID => $address->getCountryId(),
            Address::KEY_ID => (int)$address->getId(),
            Address::KEY_CUSTOMER_ID => $address->getCustomerId(),
            Address::KEY_REGION => [
                Region::REGION => $address->getRegion(),
                Region::REGION_ID => $address->getRegionId(),
                Region::REGION_CODE => $address->getRegionCode(),
            ],
            Address::KEY_STREET => $address->getStreet(),
            Address::KEY_COMPANY => $address->getCompany(),
            Address::KEY_TELEPHONE => $address->getTelephone(),
            Address::KEY_POSTCODE => $address->getPostcode(),
            Address::KEY_CITY => $address->getCity(),
            Address::KEY_FIRSTNAME => $address->getFirstname(),
            Address::KEY_LASTNAME => $address->getLastname(),
            Address::KEY_EMAIL => $address->getEmail(),
            Address::CUSTOM_ATTRIBUTES_KEY => [['attribute_code' => 'disable_auto_group_change', 'value' => null]],
        ];

        $cartId = $quote->getId();

        $serviceInfo = $this->getServiceInfo();
        $serviceInfo['rest']['resourcePath'] = str_replace('{cart_id}', $cartId, $serviceInfo['rest']['resourcePath']);

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            unset($data[Address::KEY_PREFIX]);
            unset($data[Address::KEY_SUFFIX]);
            unset($data[Address::KEY_MIDDLENAME]);
            unset($data[Address::KEY_FAX]);
            unset($data[Address::KEY_VAT_ID]);
        }

        $requestData = ["cartId" => $cartId];
        $this->assertEquals($data, $this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cart contains virtual product(s) only. Shipping address is not applicable
     */
    public function testGetAddressOfQuoteWithVirtualProduct()
    {
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        $serviceInfo = $this->getServiceInfo();
        $serviceInfo['rest']['resourcePath'] = str_replace('{cart_id}', $cartId, $serviceInfo['rest']['resourcePath']);

        $this->_webApiCall($serviceInfo, ["cartId" => $cartId]);
    }

    /**
     * @return array
     */
    protected function getServiceInfo()
    {
        return $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '{cart_id}/shipping-address',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAddress',
            ],
        ];
    }
}
