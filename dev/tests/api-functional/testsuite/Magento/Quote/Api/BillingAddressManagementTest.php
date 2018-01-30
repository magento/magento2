<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class BillingAddressManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteBillingAddressManagementV1';
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
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $quote->getBillingAddress();

        $data = [
            AddressInterface::KEY_ID => (int)$address->getId(),
            AddressInterface::KEY_REGION => $address->getRegion(),
            AddressInterface::KEY_REGION_ID => $address->getRegionId(),
            AddressInterface::KEY_REGION_CODE => $address->getRegionCode(),
            AddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
            AddressInterface::KEY_STREET => $address->getStreet(),
            AddressInterface::KEY_COMPANY => $address->getCompany(),
            AddressInterface::KEY_TELEPHONE => $address->getTelephone(),
            AddressInterface::KEY_POSTCODE => $address->getPostcode(),
            AddressInterface::KEY_CITY => $address->getCity(),
            AddressInterface::KEY_FIRSTNAME => $address->getFirstname(),
            AddressInterface::KEY_LASTNAME => $address->getLastname(),
            AddressInterface::KEY_CUSTOMER_ID => $address->getCustomerId(),
            AddressInterface::KEY_EMAIL => $address->getEmail(),
            AddressInterface::SAME_AS_BILLING => $address->getSameAsBilling(),
            AddressInterface::CUSTOMER_ADDRESS_ID => $address->getCustomerAddressId(),
            AddressInterface::SAVE_IN_ADDRESS_BOOK => $address->getSaveInAddressBook()

        ];

        $cartId = $quote->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/billing-address',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $response = $this->_webApiCall($serviceInfo, $requestData);

        asort($data);
        asort($response);
        $this->assertEquals($data, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetAddress()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $quote->getId() . '/billing-address',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Assign',
            ],
        ];

        $addressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => '',
            'company' => 'Magento Commerce Inc.',
            'street' => ['Typical Street', 'Tiny House 18'],
            'city' => 'Big City',
            'region_id' => 12,
            'region' => 'California',
            'region_code' => 'CA',
            'postcode' => '0985432',
            'country_id' => 'US',
            'telephone' => '88776655',
            'fax' => '44332255',
        ];
        $requestData = [
            "cartId" => $quote->getId(),
            'address' => $addressData,
        ];

        $addressId = $this->_webApiCall($serviceInfo, $requestData);

        //reset $quote to reload data
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $address = $quote->getBillingAddress();
        $address->getRegionCode();
        $savedData  = $address->getData();
        $this->assertEquals($addressId, $savedData['address_id']);
        //custom checks for street, region and address_type
        foreach ($addressData['street'] as $streetLine) {
            $this->assertContains($streetLine, $quote->getBillingAddress()->getStreet());
        }
        unset($addressData['street']);
        unset($addressData['email']);
        $this->assertEquals('billing', $savedData['address_type']);
        //check the rest of fields
        foreach ($addressData as $key => $value) {
            $this->assertEquals($value, $savedData[$key]);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetMyAddress()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $quote->getBillingAddress();

        $data = [
            AddressInterface::KEY_ID => (int)$address->getId(),
            AddressInterface::KEY_REGION => $address->getRegion(),
            AddressInterface::KEY_REGION_ID => $address->getRegionId(),
            AddressInterface::KEY_REGION_CODE => $address->getRegionCode(),
            AddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
            AddressInterface::KEY_STREET => $address->getStreet(),
            AddressInterface::KEY_COMPANY => $address->getCompany(),
            AddressInterface::KEY_TELEPHONE => $address->getTelephone(),
            AddressInterface::KEY_POSTCODE => $address->getPostcode(),
            AddressInterface::KEY_CITY => $address->getCity(),
            AddressInterface::KEY_FIRSTNAME => $address->getFirstname(),
            AddressInterface::KEY_LASTNAME => $address->getLastname(),
            AddressInterface::KEY_CUSTOMER_ID => $address->getCustomerId(),
            AddressInterface::KEY_EMAIL => $address->getEmail(),
            AddressInterface::SAME_AS_BILLING => $address->getSameAsBilling(),
            AddressInterface::CUSTOMER_ADDRESS_ID => $address->getCustomerAddressId(),
            AddressInterface::SAVE_IN_ADDRESS_BOOK => $address->getSaveInAddressBook()

        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/billing-address',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ],
        ];

        $response = $this->_webApiCall($serviceInfo);

        asort($data);
        asort($response);
        $this->assertEquals($data, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSetMyAddress()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            'Magento\Integration\Api\CustomerTokenServiceInterface'
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/billing-address',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $token
            ],
        ];

        $addressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'company' => 'Magento Commerce Inc.',
            'street' => ['Typical Street', 'Tiny House 18'],
            'city' => 'Big City',
            'region_id' => 12,
            'region' => 'California',
            'region_code' => 'CA',
            'postcode' => '0985432',
            'country_id' => 'US',
            'telephone' => '88776655',
            'fax' => '44332255',
        ];
        $requestData = [
            'address' => $addressData,
        ];

        $addressId = $this->_webApiCall($serviceInfo, $requestData);

        //reset $quote to reload data
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $address = $quote->getBillingAddress();
        $address->getRegionCode();
        $savedData = $address->getData();
        $this->assertEquals($addressId, $savedData['address_id']);
        //custom checks for street, region and address_type
        foreach ($addressData['street'] as $streetLine) {
            $this->assertContains($streetLine, $quote->getBillingAddress()->getStreet());
        }
        unset($addressData['street']);
        $this->assertEquals('billing', $savedData['address_type']);
        //check the rest of fields
        foreach ($addressData as $key => $value) {
            $this->assertEquals($value, $savedData[$key]);
        }
    }
}
