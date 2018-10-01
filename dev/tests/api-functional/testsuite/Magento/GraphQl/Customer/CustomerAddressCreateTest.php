<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use PHPUnit\Framework\TestResult;

class CustomerAddressCreateTest extends GraphQlAbstract
{

    /**
     * Verify customers with valid credentials create new address
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddCustomerAddressWithValidCredentials()
    {
        $newAddress = [
            'region' => [
                'region' => 'Alaska',
                'region_id' => 4,
                'region_code' => 'AK'
            ],
            'region_id' => 4,
            'country_id' => 'US',
            'street' => ['Line 1 Street', 'Line 2'],
            'company' => 'Company name',
            'telephone' => '123456789',
            'fax' => '123123123',
            'postcode' => '7777',
            'city' => 'City Name',
            'firstname' => 'Adam',
            'lastname' => 'Phillis',
            'middlename' => 'A',
            'prefix' => 'Mr.',
            'suffix' => 'Jr.',
            'vat_id' => '1',
            'default_shipping' => false,
            'default_billing' => false
        ];
        $defaultShippingText = $newAddress['default_shipping'] ? "true": "false";
        $defaultBillingText = $newAddress['default_billing'] ? "true": "false";
        $mutation
            = <<<MUTATION
mutation {
  customerAddressCreate(input: {
    region: {
        region: "{$newAddress['region']['region']}"
        region_id: {$newAddress['region']['region_id']}
        region_code: "{$newAddress['region']['region_code']}"
    }
    region_id: {$newAddress['region_id']}
    country_id: {$newAddress['country_id']}
    street: ["{$newAddress['street'][0]}","{$newAddress['street'][1]}"]
    company: "{$newAddress['company']}"
    telephone: "{$newAddress['telephone']}"
    fax: "{$newAddress['fax']}"
    postcode: "{$newAddress['postcode']}"
    city: "{$newAddress['city']}"
    firstname: "{$newAddress['firstname']}"
    lastname: "{$newAddress['lastname']}"
    middlename: "{$newAddress['middlename']}"
    prefix: "{$newAddress['prefix']}"
    suffix: "{$newAddress['suffix']}"
    vat_id: "{$newAddress['vat_id']}"
    default_shipping: {$defaultShippingText}
    default_billing: {$defaultBillingText}
  }) {
    id
    customer_id
    region {
      region
      region_id
      region_code
    }
    region_id
    country_id
    street
    company
    telephone
    fax
    postcode
    city
    firstname
    lastname
    middlename
    prefix
    suffix
    vat_id
    default_shipping
    default_billing
  }
}
MUTATION;
        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($userName);
        $response = $this->graphQlQuery($mutation, [], '', $headerMap);
        $this->assertArrayHasKey('customerAddressCreate', $response);
        $this->assertArrayHasKey('customer_id', $response['customerAddressCreate']);
        $this->assertEquals($customer->getId(), $response['customerAddressCreate']['customer_id']);
        $this->assertArrayHasKey('id', $response['customerAddressCreate']);
        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $addressId = $response['customerAddressCreate']['id'];
        $address = $addressRepository->getById($addressId);
        $this->assertEquals($address->getId(), $response['customerAddressCreate']['id']);
        $this->assertCustomerAddressesFields($address, $response['customerAddressCreate']);
        $this->assertCustomerAddressesFields($address, $newAddress);
    }

    /**
     * Verify customers without credentials create new address
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddCustomerAddressWithoutCredentials()
    {
        $mutation
            = <<<MUTATION
mutation{
  customerAddressCreate(input: {
  	prefix: "Mr."
    firstname: "John"
    middlename: "A"
    lastname: "Smith"
    telephone: "123456789"
    street: ["Line 1", "Line 2"]
    city: "Test City"
    region_id: 1
    country_id: US
    postcode: "9999"
    default_shipping: true
    default_billing: false
  }) {
    id
    customer_id
    firstname
    lastname
  }
}
MUTATION;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors:' . ' ' .
            'Current customer does not have access to the resource "customer_address"');
        $this->graphQlQuery($mutation);
    }

    /**
     * Verify customers with valid credentials create new address
     * with missing required Firstname attribute
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAddCustomerAddressWithMissingAttributeWithValidCredentials()
    {
        $newAddress = [
            'region' => [
                'region' => 'Alaska',
                'region_id' => 4,
                'region_code' => 'AK'
            ],
            'region_id' => 4,
            'country_id' => 'US',
            'street' => ['Line 1 Street', 'Line 2'],
            'company' => 'Company name',
            'telephone' => '123456789',
            'fax' => '123123123',
            'postcode' => '7777',
            'city' => 'City Name',
            'firstname' => '', //empty firstname
            'lastname' => 'Phillis',
            'middlename' => 'A',
            'prefix' => 'Mr.',
            'suffix' => 'Jr.',
            'vat_id' => '1',
            'default_shipping' => false,
            'default_billing' => false
        ];
        $defaultShippingText = $newAddress['default_shipping'] ? "true": "false";
        $defaultBillingText = $newAddress['default_billing'] ? "true": "false";
        $mutation
            = <<<MUTATION
mutation {
  customerAddressCreate(input: {
    region: {
        region: "{$newAddress['region']['region']}"
        region_id: {$newAddress['region']['region_id']}
        region_code: "{$newAddress['region']['region_code']}"
    }
    region_id: {$newAddress['region_id']}
    country_id: {$newAddress['country_id']}
    street: ["{$newAddress['street'][0]}","{$newAddress['street'][1]}"]
    company: "{$newAddress['company']}"
    telephone: "{$newAddress['telephone']}"
    fax: "{$newAddress['fax']}"
    postcode: "{$newAddress['postcode']}"
    city: "{$newAddress['city']}"
    firstname: "{$newAddress['firstname']}"
    lastname: "{$newAddress['lastname']}"
    middlename: "{$newAddress['middlename']}"
    prefix: "{$newAddress['prefix']}"
    suffix: "{$newAddress['suffix']}"
    vat_id: "{$newAddress['vat_id']}"
    default_shipping: {$defaultShippingText}
    default_billing: {$defaultBillingText}
  }) {
    id
    customer_id
    region {
      region
      region_id
      region_code
    }
    region_id
    country_id
    street
    company
    telephone
    fax
    postcode
    city
    firstname
    lastname
    middlename
    prefix
    suffix
    vat_id
    default_shipping
    default_billing
  }
}
MUTATION;
        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors:' . ' ' .
            'Required parameter firstname is missing');
        $this->graphQlQuery($mutation, [], '', $headerMap);
    }

    /**
     * Verify the fields for Customer address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @param array $actualResponse
     */
    private function assertCustomerAddressesFields($address, $actualResponse)
    {
        /** @var  $addresses */
        $assertionMap = [
            ['response_field' => 'region_id', 'expected_value' => $address->getRegionId()],
            ['response_field' => 'country_id', 'expected_value' => $address->getCountryId()],
            ['response_field' => 'street', 'expected_value' => $address->getStreet()],
            ['response_field' => 'company', 'expected_value' => $address->getCompany()],
            ['response_field' => 'telephone', 'expected_value' => $address->getTelephone()],
            ['response_field' => 'fax', 'expected_value' => $address->getFax()],
            ['response_field' => 'postcode', 'expected_value' => $address->getPostcode()],
            ['response_field' => 'city', 'expected_value' => $address->getCity()],
            ['response_field' => 'firstname', 'expected_value' => $address->getFirstname()],
            ['response_field' => 'lastname', 'expected_value' => $address->getLastname()],
            ['response_field' => 'middlename', 'expected_value' => $address->getMiddlename()],
            ['response_field' => 'prefix', 'expected_value' => $address->getPrefix()],
            ['response_field' => 'suffix', 'expected_value' => $address->getSuffix()],
            ['response_field' => 'vat_id', 'expected_value' => $address->getVatId()],
            ['response_field' => 'default_shipping', 'expected_value' => (bool)$address->isDefaultShipping()],
            ['response_field' => 'default_billing', 'expected_value' => (bool)$address->isDefaultBilling()],
        ];
        $this->assertResponseFields($actualResponse, $assertionMap);
        $this->assertTrue(is_array([$actualResponse['region']]), "region field must be of an array type.");
        $assertionRegionMap = [
            ['response_field' => 'region', 'expected_value' => $address->getRegion()->getRegion()],
            ['response_field' => 'region_code', 'expected_value' => $address->getRegion()->getRegionCode()],
            ['response_field' => 'region_id', 'expected_value' => $address->getRegion()->getRegionId()]
        ];
        $this->assertResponseFields($actualResponse['region'], $assertionRegionMap);
    }
}
