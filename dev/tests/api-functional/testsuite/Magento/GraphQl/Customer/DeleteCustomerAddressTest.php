<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

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

    protected function setUp()
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->addressRepository = Bootstrap::getObjectManager()->get(AddressRepositoryInterface::class);
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
        $response = $this->graphQlQuery($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
        $this->assertArrayHasKey('deleteCustomerAddress', $response);
        $this->assertEquals(true, $response['deleteCustomerAddress']);
    }

    /**
     */
    public function testDeleteCustomerAddressIfUserIsNotAuthorized()
    {
        $this->setExpectedException(\Exception::class, 'The current customer isn\'t authorized.');

        $addressId = 1;
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;
        $this->graphQlQuery($mutation);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     */
    public function testDeleteDefaultShippingCustomerAddress()
    {
        $this->setExpectedException(\Exception::class, 'Customer Address 2 is set as default shipping address and can not be deleted');

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
        $this->graphQlQuery($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     *
     */
    public function testDeleteDefaultBillingCustomerAddress()
    {
        $this->setExpectedException(\Exception::class, 'Customer Address 2 is set as default billing address and can not be deleted');

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
        $this->graphQlQuery($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testDeleteNonExistCustomerAddress()
    {
        $this->setExpectedException(\Exception::class, 'Address id 9999 does not exist.');

        $userName = 'customer@example.com';
        $password = 'password';
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: 9999)
}
MUTATION;
        $this->graphQlQuery($mutation, [], '', $this->getCustomerAuthHeaders($userName, $password));
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
