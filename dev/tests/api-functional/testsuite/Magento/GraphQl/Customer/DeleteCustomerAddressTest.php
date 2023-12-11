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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Delete customer address tests
 */
class DeleteCustomerAddressTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testDeleteCustomerAddress()
    {
        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 2;

        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
        $response = $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
        $this->assertArrayHasKey('deleteCustomerAddress', $response);
        $this->assertTrue($response['deleteCustomerAddress']);
    }

    /**
     */
    public function testDeleteCustomerAddressIfUserIsNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $addressId = 1;
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
        $this->graphQlMutation($mutation);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     */
    public function testDeleteDefaultShippingCustomerAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Customer Address 2 is set as default shipping address and can not be deleted');

        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 2;

        $address = $this->addressRepository->getById($addressId);
        $address->setIsDefaultShipping(true);
        $this->addressRepository->save($address);

        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     */
    public function testDeleteDefaultBillingCustomerAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Customer Address 2 is set as default billing address and can not be deleted');

        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 2;

        $address = $this->addressRepository->getById($addressId);
        $address->setIsDefaultBilling(true);
        $this->addressRepository->save($address);

        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testDeleteNonExistCustomerAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a address with ID "9999"');

        $userName = 'customer@example.com';
        $password = 'password';
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: 9999)
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * Delete address with missing ID input.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @throws Exception
     */
    public function testDeleteCustomerAddressWithMissingData()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Syntax Error: Expected Name, found )');

        $userName = 'customer@example.com';
        $password = 'password';
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress()
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * Delete address with incorrect ID input type.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @throws Exception
     */
    public function testDeleteCustomerAddressWithIncorrectIdType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Expected type Int!, found "".');

        $this->markTestSkipped(
            'Type validation returns wrong message https://github.com/magento/graphql-ce/issues/735'
        );
        $userName = 'customer@example.com';
        $password = 'password';
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: "string")
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     */
    public function testDeleteAnotherCustomerAddress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Current customer does not have permission to address with ID "2"');

        $userName = 'customer_two@example.com';
        $password = 'password';
        $addressId = 2;

        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     */
    public function testDeleteCustomerAddressIfAccountIsLocked()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account is locked');

        $this->markTestSkipped('https://github.com/magento/graphql-ce/issues/750');

        $userName = 'customer@example.com';
        $password = 'password';
        $addressId = 2;

        $this->lockCustomer->execute(1);

        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
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
}
