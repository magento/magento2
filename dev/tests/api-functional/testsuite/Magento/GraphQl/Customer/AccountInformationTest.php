<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class AccountInformationTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->accountManagement = Bootstrap::getObjectManager()->get(AccountManagementInterface::class);
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->customerAuthUpdate = Bootstrap::getObjectManager()->get(CustomerAuthUpdate::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetAccountInformation()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertEquals('John', $response['customer']['firstname']);
        $this->assertEquals('Smith', $response['customer']['lastname']);
        $this->assertEquals($currentEmail, $response['customer']['email']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testGetAccountInformationIfUserIsNotAuthorized()
    {
        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage The account is locked.
     */
    public function testGetAccountInformationIfCustomerIsLocked()
    {
        $this->lockCustomer(1);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateAccountInformation()
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
     * @expectedException \Exception
     * @expectedExceptionMessage "input" value should be specified
     */
    public function testUpdateAccountInformationIfInputDataIsEmpty()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testUpdateAccountInformationIfUserIsNotAuthorized()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage The account is locked.
     */
    public function testUpdateAccountInformationIfCustomerIsLocked()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage For changing "email" you should provide current "password".
     */
    public function testUpdateEmailIfPasswordIsMissed()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage The password doesn't match this account. Verify the password and try again.
     */
    public function testUpdateEmailIfPasswordIsInvalid()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage A customer with the same email address already exists in an associated website.
     */
    public function testUpdateEmailIfEmailAlreadyExists()
    {
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
