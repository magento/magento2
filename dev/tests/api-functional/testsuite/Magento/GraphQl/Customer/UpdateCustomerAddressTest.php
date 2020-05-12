<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Update customer address tests
 */
class UpdateCustomerAddressTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->addressRepository = Bootstrap::getObjectManager()->get(AddressRepositoryInterface::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateCustomerAddress()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 1;

        $mutation = $this->getMutation($addressId);

        $response = $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
        $this->assertArrayHasKey('updateCustomerAddress', $response);
        $this->assertArrayHasKey('customer_id', $response['updateCustomerAddress']);
        $this->assertNull($response['updateCustomerAddress']['customer_id']);
        $this->assertArrayHasKey('id', $response['updateCustomerAddress']);

        $address = $this->addressRepository->getById($addressId);
        $this->assertEquals($address->getId(), $response['updateCustomerAddress']['id']);
        $this->assertCustomerAddressesFields($address, $response['updateCustomerAddress']);
        $updateAddress = $this->getAddressData();
        $this->assertCustomerAddressesFields($address, $updateAddress);
    }

    /**
     * Test case for deprecated `country_id` field.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateCustomerAddressWithCountryId()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 1;

        $updateAddress = $this->getAddressData();

        $mutation =  $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(id: {$addressId}, input: {
    region: {
        region: "{$updateAddress['region']['region']}"
        region_id: {$updateAddress['region']['region_id']}
        region_code: "{$updateAddress['region']['region_code']}"
    }
    country_id: {$updateAddress['country_code']}
    street: ["{$updateAddress['street'][0]}","{$updateAddress['street'][1]}"]
    company: "{$updateAddress['company']}"
    telephone: "{$updateAddress['telephone']}"
    fax: "{$updateAddress['fax']}"
    postcode: "{$updateAddress['postcode']}"
    city: "{$updateAddress['city']}"
    firstname: "{$updateAddress['firstname']}"
    lastname: "{$updateAddress['lastname']}"
    middlename: "{$updateAddress['middlename']}"
    prefix: "{$updateAddress['prefix']}"
    suffix: "{$updateAddress['suffix']}"
    vat_id: "{$updateAddress['vat_id']}"
    default_shipping: true
    default_billing: true
  }) {
    country_id
  }
}
MUTATION;

        $response = $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
        $this->assertArrayHasKey('updateCustomerAddress', $response);
        $this->assertEquals($updateAddress['country_code'], $response['updateCustomerAddress']['country_id']);
    }

    /**
     */
    public function testUpdateCustomerAddressIfUserIsNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $addressId = 1;
        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(id:{$addressId}, input: {
    city: "New City"
    postcode: "5555"
  }) {
    id
  }
}
MUTATION;
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customers with credentials update address
     * with missing required Firstname attribute
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testUpdateCustomerAddressWithMissingAttribute()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameters are missing: firstname');

        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 1;

        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(id: {$addressId}, input: {
    firstname: ""
    lastname: "Phillis"
  }) {
    id
  }
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * Test custom attributes of the customer's address
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_address_custom_attribute.php
     */
    public function testUpdateCustomerAddressHasCustomAttributes()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 1;
        $attributes = [
            [
                'attribute_code' => 'custom_attribute1',
                'value'=> '[new-value1,new-value2]'
            ],
            [
                'attribute_code' => 'custom_attribute2',
                'value'=> '"new-value3"'
            ]
        ];
        $attributesFragment = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($attributes));
        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(
    id: {$addressId}
    input: {
      custom_attributes: {$attributesFragment}
    }
  ) {
    custom_attributes {
      attribute_code
      value
    }
  }
}
MUTATION;

        $response = $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
        $this->assertEquals($attributes, $response['updateCustomerAddress']['custom_attributes']);
    }

    /**
     * Verify the fields for Customer address
     *
     * @param AddressInterface $address
     * @param array $actualResponse
     * @param string $countryFieldName
     */
    private function assertCustomerAddressesFields(
        AddressInterface $address,
        $actualResponse
    ): void {
        /** @var  $addresses */
        $assertionMap = [
            ['response_field' => 'country_code', 'expected_value' => $address->getCountryId()],
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
        $this->assertIsArray([$actualResponse['region']], "region field must be of an array type.");
        $assertionRegionMap = [
            ['response_field' => 'region', 'expected_value' => $address->getRegion()->getRegion()],
            ['response_field' => 'region_code', 'expected_value' => $address->getRegion()->getRegionCode()],
            ['response_field' => 'region_id', 'expected_value' => $address->getRegion()->getRegionId()]
        ];
        $this->assertResponseFields($actualResponse['region'], $assertionRegionMap);
    }

    /**
     * Update address with missing ID input.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateCustomerAddressWithMissingId()
    {
        $userName = 'customer@example.com';
        $password = 'password';

        $updateAddress = $this->getAddressData();
        $defaultShippingText = $updateAddress['default_shipping'] ? 'true' : 'false';
        $defaultBillingText = $updateAddress['default_billing'] ? 'true' : 'false';

        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(, input: {
    region: {
        region: "{$updateAddress['region']['region']}"
        region_id: {$updateAddress['region']['region_id']}
        region_code: "{$updateAddress['region']['region_code']}"
    }
    country_code: {$updateAddress['country_code']}
    street: ["{$updateAddress['street'][0]}","{$updateAddress['street'][1]}"]
    company: "{$updateAddress['company']}"
    telephone: "{$updateAddress['telephone']}"
    fax: "{$updateAddress['fax']}"
    postcode: "{$updateAddress['postcode']}"
    city: "{$updateAddress['city']}"
    firstname: "{$updateAddress['firstname']}"
    lastname: "{$updateAddress['lastname']}"
    middlename: "{$updateAddress['middlename']}"
    prefix: "{$updateAddress['prefix']}"
    suffix: "{$updateAddress['suffix']}"
    vat_id: "{$updateAddress['vat_id']}"
    default_shipping: {$defaultShippingText}
    default_billing: {$defaultBillingText}
  }) {
    id
  }
}
MUTATION;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Field "updateCustomerAddress" argument "id" of type "Int!" is required but not provided.'
        );
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * Update address with invalid ID input.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateCustomerAddressWithInvalidIdType()
    {
        $this->markTestSkipped(
            'Type validation returns wrong message https://github.com/magento/graphql-ce/issues/735'
        );
        $userName = 'customer@example.com';
        $password = 'password';

        $updateAddress = $this->getAddressData();
        $defaultShippingText = $updateAddress['default_shipping'] ? 'true' : 'false';
        $defaultBillingText = $updateAddress['default_billing'] ? 'true' : 'false';

        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(id: "", input: {
    region: {
        region: "{$updateAddress['region']['region']}"
        region_id: {$updateAddress['region']['region_id']}
        region_code: "{$updateAddress['region']['region_code']}"
    }
    country_code: {$updateAddress['country_code']}
    street: ["{$updateAddress['street'][0]}","{$updateAddress['street'][1]}"]
    company: "{$updateAddress['company']}"
    telephone: "{$updateAddress['telephone']}"
    fax: "{$updateAddress['fax']}"
    postcode: "{$updateAddress['postcode']}"
    city: "{$updateAddress['city']}"
    firstname: "{$updateAddress['firstname']}"
    lastname: "{$updateAddress['lastname']}"
    middlename: "{$updateAddress['middlename']}"
    prefix: "{$updateAddress['prefix']}"
    suffix: "{$updateAddress['suffix']}"
    vat_id: "{$updateAddress['vat_id']}"
    default_shipping: {$defaultShippingText}
    default_billing: {$defaultBillingText}
  }) {
    id
  }
}
MUTATION;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Expected type Int!, found ""');
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * Update address with invalid input
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @dataProvider invalidInputDataProvider
     * @param string $input
     * @param string $exceptionMessage
     */
    public function testUpdateCustomerAddressWithInvalidInput(string $input, string $exceptionMessage)
    {
        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 1;

        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(id: {$addressId}, $input) {
    id
  }
}
MUTATION;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @return array
     */
    public function invalidInputDataProvider()
    {
        return [
            ['', '"input" value must be specified'],
            ['input: ""', 'requires type CustomerAddressInput, found ""'],
            ['input: "foo"', 'requires type CustomerAddressInput, found "foo"']
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testUpdateNotExistingCustomerAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a address with ID "9999"');

        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 9999;

        $mutation = $this->getMutation($addressId);

        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testUpdateAnotherCustomerAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Current customer does not have permission to address with ID "1"');

        $userName = 'customer_two@example.com';
        $password = 'password';
        $addressId = 1;

        $mutation = $this->getMutation($addressId);

        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testUpdateCustomerAddressIfAccountIsLocked()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account is locked.');

        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/750');

        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 1;
        $this->lockCustomer->execute(1);

        $mutation = $this->getMutation($addressId);

        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
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

    /**
     * @return array
     */
    private function getAddressData(): array
    {
        return [
            'region' => [
                'region' => 'Alberta',
                'region_id' => 66,
                'region_code' => 'AB'
            ],
            'country_code' => 'CA',
            'street' => ['Line 1 Street', 'Line 2'],
            'company' => 'Company Name',
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
            'default_billing' => true
        ];
    }

    /**
     * @param int $addressId
     * @return string
     */
    private function getMutation(int $addressId): string
    {
        $updateAddress = $this->getAddressData();
        $defaultShippingText = $updateAddress['default_shipping'] ? "true" : "false";
        $defaultBillingText = $updateAddress['default_billing'] ? "true" : "false";

        $mutation
            = <<<MUTATION
mutation {
  updateCustomerAddress(id: {$addressId}, input: {
    region: {
        region: "{$updateAddress['region']['region']}"
        region_id: {$updateAddress['region']['region_id']}
        region_code: "{$updateAddress['region']['region_code']}"
    }
    country_code: {$updateAddress['country_code']}
    street: ["{$updateAddress['street'][0]}","{$updateAddress['street'][1]}"]
    company: "{$updateAddress['company']}"
    telephone: "{$updateAddress['telephone']}"
    fax: "{$updateAddress['fax']}"
    postcode: "{$updateAddress['postcode']}"
    city: "{$updateAddress['city']}"
    firstname: "{$updateAddress['firstname']}"
    lastname: "{$updateAddress['lastname']}"
    middlename: "{$updateAddress['middlename']}"
    prefix: "{$updateAddress['prefix']}"
    suffix: "{$updateAddress['suffix']}"
    vat_id: "{$updateAddress['vat_id']}"
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
    country_code
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
        return $mutation;
    }
}
