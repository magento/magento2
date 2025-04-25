<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for new update customer endpoint
 */
#[
    DataFixture(
        Customer::class,
        [
            'email' => 'customer@example.com',
        ],
        'customer'
    )
]
class UpdateCustomerV2Test extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    /**
     * @var CustomerInterface|null
     */
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
        $this->customer = DataFixtureStorageManager::getStorage()->get('customer');
    }

    public function testUpdateCustomer(): void
    {
        $currentPassword = 'password';

        $newPrefix = 'Dr';
        $newFirstname = 'Richard';
        $newMiddlename = 'Riley';
        $newLastname = 'Rowe';
        $newSuffix = 'III';
        $newDob = '3/11/1972';
        $newTaxVat = 'GQL1234567';
        $newGender = 2;

        $query = <<<QUERY
mutation {
    updateCustomerV2(
        input: {
            prefix: "{$newPrefix}"
            firstname: "{$newFirstname}"
            middlename: "{$newMiddlename}"
            lastname: "{$newLastname}"
            suffix: "{$newSuffix}"
            date_of_birth: "{$newDob}"
            taxvat: "{$newTaxVat}"
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
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $currentPassword)
        );

        $this->assertEquals($newPrefix, $response['updateCustomerV2']['customer']['prefix']);
        $this->assertEquals($newFirstname, $response['updateCustomerV2']['customer']['firstname']);
        $this->assertEquals($newMiddlename, $response['updateCustomerV2']['customer']['middlename']);
        $this->assertEquals($newLastname, $response['updateCustomerV2']['customer']['lastname']);
        $this->assertEquals($newSuffix, $response['updateCustomerV2']['customer']['suffix']);
        $this->assertEquals($newDob, $response['updateCustomerV2']['customer']['date_of_birth']);
        $this->assertEquals($newTaxVat, $response['updateCustomerV2']['customer']['taxvat']);
        $this->assertEquals($newGender, $response['updateCustomerV2']['customer']['gender']);
    }

    public function testUpdateCustomerIfInputDataIsEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"input" value should be specified');

        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomerV2(
        input: {

        }
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $currentPassword)
        );
    }

    public function testUpdateCustomerIfUserIsNotAuthorized(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $newFirstname = 'Richard';

        $query = <<<QUERY
mutation {
    updateCustomerV2(
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

    public function testUpdateCustomerIfAccountIsLocked(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The account is locked.');

        $this->lockCustomer->execute((int)$this->customer->getId());

        $currentPassword = 'password';
        $newFirstname = 'Richard';

        $query = <<<QUERY
mutation {
    updateCustomerV2(
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
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $currentPassword)
        );
    }

    public function testEmptyCustomerName(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"First Name" is a required value.');

        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomerV2(
        input: {
            firstname: ""
        }
    ) {
        customer {
            email
        }
    }
}
QUERY;
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), $currentPassword)
        );
    }

    public function testEmptyCustomerLastName(): void
    {
        $query = <<<QUERY
mutation {
    updateCustomerV2(
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
        $this->expectExceptionMessage('"Last Name" is a required value.');

        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), 'password')
        );
    }

    public function testUpdateCustomerIfDobIsInvalid(): void
    {
        $invalidDob = 'bla-bla-bla';

        $query = <<<QUERY
mutation {
    updateCustomerV2(
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

        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($this->customer->getEmail(), 'password')
        );
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
