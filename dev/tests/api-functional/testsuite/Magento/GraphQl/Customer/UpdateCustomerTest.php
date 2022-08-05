<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for update customer
 */
class UpdateCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerAuthUpdate = Bootstrap::getObjectManager()->get(CustomerAuthUpdate::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomer()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $newPrefix = 'Dr';
        $newFirstname = 'Richard';
        $newMiddlename = 'Riley';
        $newLastname = 'Rowe';
        $newSuffix = 'III';
        $newDob = '3/11/1972';
        $newTaxVat = 'GQL1234567';
        $newGender = 2;
        $newEmail = 'customer_updated@example.com';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            prefix: "{$newPrefix}"
            firstname: "{$newFirstname}"
            middlename: "{$newMiddlename}"
            lastname: "{$newLastname}"
            suffix: "{$newSuffix}"
            date_of_birth: "{$newDob}"
            taxvat: "{$newTaxVat}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
            gender: {$newGender}
        }
    ) {
        customer {
            prefix
            firstname
            middlename
            lastname
            suffix
            date_of_birth
            taxvat
            email
            gender
        }
    }
}
QUERY;
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertEquals($newPrefix, $response['updateCustomer']['customer']['prefix']);
        $this->assertEquals($newFirstname, $response['updateCustomer']['customer']['firstname']);
        $this->assertEquals($newMiddlename, $response['updateCustomer']['customer']['middlename']);
        $this->assertEquals($newLastname, $response['updateCustomer']['customer']['lastname']);
        $this->assertEquals($newSuffix, $response['updateCustomer']['customer']['suffix']);
        $this->assertEquals($newDob, $response['updateCustomer']['customer']['date_of_birth']);
        $this->assertEquals($newTaxVat, $response['updateCustomer']['customer']['taxvat']);
        $this->assertEquals($newEmail, $response['updateCustomer']['customer']['email']);
        $this->assertEquals($newGender, $response['updateCustomer']['customer']['gender']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerIfInputDataIsEmpty()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"input" value should be specified');

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
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     */
    public function testUpdateCustomerIfUserIsNotAuthorized()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

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
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerIfAccountIsLocked()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The account is locked.');

        $this->lockCustomer->execute(1);

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
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateEmailIfPasswordIsMissed()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Provide the current "password" to change "email".');

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
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateEmailIfPasswordIsInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid login or password.');

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
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testUpdateEmailIfEmailAlreadyExists()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $existedEmail = 'customer_two@example.com';
        $firstname = 'Richard';
        $lastname = 'Rowe';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            email: "{$existedEmail}"
            password: "{$currentPassword}"
            firstname: "{$firstname}"
            lastname: "{$lastname}"
        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateEmailIfEmailIsInvalid()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $invalidEmail = 'customer.example.com';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            email: "{$invalidEmail}"
            password: "{$currentPassword}"
        }
    ) {
        customer {
            email
        }
    }
}
QUERY;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"' . $invalidEmail . '" is not a valid email address.');

        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testEmptyCustomerName()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Required parameters are missing: First Name');

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            email: "{$currentEmail}"
            password: "{$currentPassword}"
            firstname: ""
        }
    ) {
        customer {
            email
        }
    }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testEmptyCustomerLastName()
    {
        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            lastname: ""
        }
    ) {
        customer {
            lastname
        }
    }
}
QUERY;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Required parameters are missing: Last Name');

        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders('customer@example.com', 'password'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerWithIncorrectGender()
    {
        $gender = 5;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"' . $gender . '" is not a valid gender value.');

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            gender: {$gender}
        }
    ) {
        customer {
            gender
        }
    }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders('customer@example.com', 'password'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerIfDobIsInvalid()
    {
        $invalidDob = 'bla-bla-bla';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            date_of_birth: "{$invalidDob}"
        }
    ) {
        customer {
            date_of_birth
        }
    }
}
QUERY;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid date');

        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders('customer@example.com', 'password'));
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
