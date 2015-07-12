<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Api;

use Magento\TestFramework\Helper\Bootstrap;

class AddressRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SOAP_SERVICE_NAME = 'customerAddressRepositoryV1';
    const SOAP_SERVICE_VERSION = 'V1';

    /** @var \Magento\Customer\Api\AddressRepositoryInterface */
    protected $addressRepository;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    protected $customerRepository;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->get(
            'Magento\Customer\Api\CustomerRepositoryInterface'
        );
        $this->addressRepository = $objectManager->get(
            'Magento\Customer\Api\AddressRepositoryInterface'
        );
        parent::setUp();
    }

    /**
     * Ensure that fixture customer and his addresses are deleted.
     */
    protected function tearDown()
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        try {
            $fixtureFirstAddressId = 1;
            $this->addressRepository->deleteById($fixtureFirstAddressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** First address fixture was not used */
        }
        try {
            $fixtureSecondAddressId = 2;
            $this->addressRepository->deleteById($fixtureSecondAddressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** Second address fixture was not used */
        }
        try {
            $fixtureCustomerId = 1;
            $this->customerRepository->deleteById($fixtureCustomerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** Customer fixture was not used */
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testGetAddress()
    {
        $fixtureAddressId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/customers/addresses/{$fixtureAddressId}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SOAP_SERVICE_NAME,
                'serviceVersion' => self::SOAP_SERVICE_VERSION,
                'operation' => self::SOAP_SERVICE_NAME . 'GetById',
            ],
        ];
        $requestData = ['addressId' => $fixtureAddressId];
        $addressData = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(
            $this->getFirstFixtureAddressData(),
            $addressData,
            "Address data is invalid."
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testDeleteAddress()
    {
        $fixtureAddressId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/addresses/{$fixtureAddressId}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SOAP_SERVICE_NAME,
                'serviceVersion' => self::SOAP_SERVICE_VERSION,
                'operation' => self::SOAP_SERVICE_NAME . 'DeleteById',
            ],
        ];
        $requestData = ['addressId' => $fixtureAddressId];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($response, 'Expected response should be true.');

        $this->setExpectedException('Magento\Framework\Exception\NoSuchEntityException', 'No such entity with addressId = 1');
        $this->addressRepository->getById($fixtureAddressId);
    }

    /**
     * Retrieve data of the first fixture address.
     *
     * @return array
     */
    protected function getFirstFixtureAddressData()
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'city' => 'CityM',
            'country_id' => 'US',
            'company' => 'CompanyName',
            'postcode' => '75477',
            'telephone' => '3468676',
            'street' => ['Green str, 67'],
            'id' => 1,
            'default_billing' => true,
            'default_shipping' => true,
            'customer_id' => '1',
            'region' => ['region' => 'Alabama', 'region_id' => 1, 'region_code' => 'AL'],
            'region_id' => 1,
        ];
    }

    /**
     * Retrieve data of the second fixture address.
     *
     * @return array
     */
    protected function getSecondFixtureAddressData()
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'city' => 'CityX',
            'country_id' => 'US',
            'postcode' => '47676',
            'telephone' => '3234676',
            'street' => ['Black str, 48'],
            'id' => 2,
            'default_billing' => false,
            'default_shipping' => false,
            'customer_id' => '1',
            'region' => ['region' => 'Alabama', 'region_id' => 1, 'region_code' => 'AL'],
            'region_id' => 1,
        ];
    }
}
