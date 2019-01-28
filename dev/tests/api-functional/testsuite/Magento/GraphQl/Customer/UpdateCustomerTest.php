<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class UpdateCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    protected function setUp()
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->customerAuthUpdate = Bootstrap::getObjectManager()->get(CustomerAuthUpdate::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomer()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $newEmail = 'customer_updated@example.com';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
        }
    ) {
        customer {
            firstname
            lastname
            email
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertEquals($newFirstname, $response['updateCustomer']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['updateCustomer']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['updateCustomer']['customer']['email']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerIfInputDataIsEmpty()
    {
        $this->setExpectedException(\Exception::class, '"input" value should be specified');

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {

        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     */
    public function testUpdateCustomerIfUserIsNotAuthorized()
    {
        $this->setExpectedException(\Exception::class, 'The current customer isn\'t authorized.');

        $newFirstname = 'Richard';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            firstname: "{$newFirstname}"
        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerIfAccountIsLocked()
    {
        $this->setExpectedException(\Exception::class, 'The account is locked.');

        $this->lockCustomer(1);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newFirstname = 'Richard';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            firstname: "{$newFirstname}"
        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateEmailIfPasswordIsMissed()
    {
        $this->setExpectedException(\Exception::class, 'Provide the current "password" to change "email".');

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newEmail = 'customer_updated@example.com';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            email: "{$newEmail}"
        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateEmailIfPasswordIsInvalid()
    {
        $this->setExpectedException(\Exception::class, 'The password doesn\'t match this account. Verify the password and try again.');

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $invalidPassword = 'invalid_password';
        $newEmail = 'customer_updated@example.com';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            email: "{$newEmail}"
            password: "{$invalidPassword}"
        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testUpdateEmailIfEmailAlreadyExists()
    {
        $this->setExpectedException(\Exception::class, 'A customer with the same email address already exists in an associated website.');

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $existedEmail = 'customer_two@example.com';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            email: "{$existedEmail}"
            password: "{$currentPassword}"
        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
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
     * @param int $customerId
     * @return void
     */
    private function lockCustomer(int $customerId): void
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerSecure->setLockExpires('2030-12-31 00:00:00');
        $this->customerAuthUpdate->saveAuth($customerId);
    }
}
