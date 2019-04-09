<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Create customer address tests
 */
class CreateCustomerAddressTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->addressRepository = Bootstrap::getObjectManager()->get(AddressRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateCustomerAddress()
    {
        $customerId = 1;
        $newAddress = [
            'region' => [
                'region' => 'Arizona',
                'region_id' => 4,
                'region_code' => 'AZ'
            ],
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
            'default_shipping' => true,
            'default_billing' => false
        ];

        $mutation
            = <<<MUTATION
mutation {
  createCustomerAddress(input: {
    region: {
        region: "{$newAddress['region']['region']}"
        region_id: {$newAddress['region']['region_id']}
        region_code: "{$newAddress['region']['region_code']}"
    }
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
    default_shipping: true
    default_billing: false
  }) {
    id
    customer_id
    region {
      region
      region_id
      region_code
    }
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

        $response = $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
        $this->assertArrayHasKey('createCustomerAddress', $response);
        $this->assertArrayHasKey('customer_id', $response['createCustomerAddress']);
        $this->assertEquals($customerId, $response['createCustomerAddress']['customer_id']);
        $this->assertArrayHasKey('id', $response['createCustomerAddress']);

        $address = $this->addressRepository->getById($response['createCustomerAddress']['id']);
        $this->assertEquals($address->getId(), $response['createCustomerAddress']['id']);
        $this->assertCustomerAddressesFields($address, $response['createCustomerAddress']);
        $this->assertCustomerAddressesFields($address, $newAddress);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testCreateCustomerAddressIfUserIsNotAuthorized()
    {
        $mutation
            = <<<MUTATION
mutation{
  createCustomerAddress(input: {
    prefix: "Mr."
    firstname: "John"
    middlename: "A"
    lastname: "Smith"
    telephone: "123456789"
    street: ["Line 1", "Line 2"]
    city: "Test City"
    region: {
        region_id: 1
    }
    country_id: US
    postcode: "9999"
    default_shipping: true
    default_billing: false
  }) {
    id
  }
}
MUTATION;
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customers with valid credentials create new address
     * with missing required Firstname attribute
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameters are missing: firstname
     */
    public function testCreateCustomerAddressWithMissingAttribute()
    {
        $mutation
            = <<<MUTATION
mutation {
  createCustomerAddress(input: {
    region: {
        region_id: 1
    }
    country_id: US
    street: ["Line 1 Street","Line 2"]
    company: "Company name"
    telephone: "123456789"
    fax: "123123123"
    postcode: "7777"
    city: "City Name"
    firstname: ""
    lastname: "Phillis"
  }) {
    id
  }
}
MUTATION;

        $userName = 'customer@example.com';
        $password = 'password';
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreateCustomerAddressWithRedundantStreetLine()
    {
        $newAddress = [
            'region' => [
                'region' => 'Arizona',
                'region_id' => 4,
                'region_code' => 'AZ'
            ],
            'country_id' => 'US',
            'street' => ['Line 1 Street', 'Line 2', 'Line 3'],
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
            'default_shipping' => true,
            'default_billing' => false
        ];

        $mutation
            = <<<MUTATION
mutation {
  createCustomerAddress(input: {
    region: {
        region: "{$newAddress['region']['region']}"
        region_id: {$newAddress['region']['region_id']}
        region_code: "{$newAddress['region']['region_code']}"
    }
    country_id: {$newAddress['country_id']}
    street: ["{$newAddress['street'][0]}","{$newAddress['street'][1]}","{$newAddress['street'][2]}"]
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
    default_shipping: true
    default_billing: false
  }) {
    id
  }
}
MUTATION;

        $userName = 'customer@example.com';
        $password = 'password';

        self::expectExceptionMessage('"Street Address" cannot contain more than 2 lines.');
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * Verify the fields for Customer address
     *
     * @param AddressInterface $address
     * @param array $actualResponse
     */
    private function assertCustomerAddressesFields(AddressInterface $address, array $actualResponse): void
    {
        /** @var  $addresses */
        $assertionMap = [
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

    /**
     * @param string $email
     * @param string $password
     * @return array
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
