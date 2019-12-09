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
        $this->assertEquals(true, $response['deleteCustomerAddress']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testDeleteCustomerAddressIfUserIsNotAuthorized()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Customer Address 2 is set as default shipping address and can not be deleted
     */
    public function testDeleteDefaultShippingCustomerAddress()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Customer Address 2 is set as default billing address and can not be deleted
     */
    public function testDeleteDefaultBillingCustomerAddress()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Could not find a address with ID "9999"
     */
    public function testDeleteNonExistCustomerAddress()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Syntax Error: Expected Name, found )
     * @throws Exception
     */
    public function testDeleteCustomerAddressWithMissingData()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Expected type Int!, found "".
     * @throws Exception
     */
    public function testDeleteCustomerAddressWithIncorrectIdType()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage Current customer does not have permission to address with ID "2"
     */
    public function testDeleteAnotherCustomerAddress()
    {
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
     * @expectedException Exception
     * @expectedExceptionMessage The account is locked
     */
    public function testDeleteCustomerAddressIfAccountIsLocked()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/750');

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
