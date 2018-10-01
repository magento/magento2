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

class CustomerAddressDeleteTest extends GraphQlAbstract
{
    /**
     * Verify customers with valid credentials with a customer bearer token
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDeleteCustomerAddressWithValidCredentials()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($userName);
        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = $customer->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = end($addresses);
        $addressId = $address->getId();
        $mutation
            = <<<MUTATION
mutation {
  customerAddressDelete(id: {$addressId})
}
MUTATION;
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($mutation, [], '', $headerMap);
        $this->assertArrayHasKey('customerAddressDelete', $response);
        $this->assertEquals(true, $response['customerAddressDelete']);
    }

    /**
     * Verify customers without credentials delete address
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDeleteCustomerAddressWithoutCredentials()
    {
        $userName = 'customer@example.com';
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($userName);
        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = $customer->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = current($addresses);
        $addressId = $address->getId();
        $mutation
            = <<<MUTATION
mutation {
  customerAddressDelete(id: {$addressId})
}
MUTATION;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors:' . ' ' .
            'Current customer does not have access to the resource "customer_address"');
        $this->graphQlQuery($mutation);
    }

    /**
     * Verify customers with valid credentials delete default shipping address
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDeleteDefaultShippingCustomerAddressWithValidCredentials()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($userName);
        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = $customer->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = end($addresses);
        $address->setIsDefaultShipping(true);
        $addressRepository = ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $addressRepository->save($address);
        $addressId = $address->getId();
        $mutation
            = <<<MUTATION
mutation {
  customerAddressDelete(id: {$addressId})
}
MUTATION;
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors:' . ' ' .
            'Customer Address ' . $addressId . ' is set as default shipping address and can not be deleted');
        $this->graphQlQuery($mutation, [], '', $headerMap);
    }

    /**
     * Verify customers with valid credentials delete default billing address
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDeleteDefaultBillingCustomerAddressWithValidCredentials()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($userName);
        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = $customer->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = end($addresses);
        $address->setIsDefaultBilling(true);
        $addressRepository = ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $addressRepository->save($address);
        $addressId = $address->getId();
        $mutation
            = <<<MUTATION
mutation {
  customerAddressDelete(id: {$addressId})
}
MUTATION;
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors:' . ' ' .
            'Customer Address ' . $addressId . ' is set as default billing address and can not be deleted');
        $this->graphQlQuery($mutation, [], '', $headerMap);
    }

    /**
     * Verify customers with valid credentials delete non exist address
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDeleteNonExistCustomerAddressWithValidCredentials()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        $mutation
            = <<<MUTATION
mutation {
  customerAddressDelete(id: 9999)
}
MUTATION;
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors:' . ' ' .
            'Address id 9999 does not exist.');
        $this->graphQlQuery($mutation, [], '', $headerMap);
    }
}
