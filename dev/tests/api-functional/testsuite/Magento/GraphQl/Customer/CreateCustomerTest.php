<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for create customer functionality
 */
class CreateCustomerTest extends GraphQlAbstract
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @throws \Exception
     */
    public function testCreateCustomerAccountWithPassword()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
            is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);

        $this->assertEquals(null, $response['createCustomer']['customer']['id']);
        $this->assertEquals($newFirstname, $response['createCustomer']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomer']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomer']['customer']['email']);
        $this->assertEquals(true, $response['createCustomer']['customer']['is_subscribed']);
    }

    /**
     * @throws \Exception
     */
    public function testCreateCustomerAccountWithoutPassword()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);

        $this->assertEquals($newFirstname, $response['createCustomer']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomer']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomer']['customer']['email']);
        $this->assertEquals(true, $response['createCustomer']['customer']['is_subscribed']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage "input" value should be specified
     */
    public function testCreateCustomerIfInputDataIsEmpty()
    {
        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
        
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Required parameters are missing: Email
     */
    public function testCreateCustomerIfEmailMissed()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            password: "{$currentPassword}"
            is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @dataProvider invalidEmailAddressDataProvider
     *
     * @param string $email
     * @throws \Exception
     */
    public function testCreateCustomerIfEmailIsNotValid(string $email)
    {
        $firstname = 'Richard';
        $lastname = 'Rowe';
        $password = 'test123#';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$firstname}"
            lastname: "{$lastname}"
            email: "{$email}"
            password: "{$password}"
            is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $this->expectExceptionMessage('"' . $email . '" is not a valid email address.');
        $this->graphQlMutation($query);
    }

    /**
     * @return array
     */
    public function invalidEmailAddressDataProvider(): array
    {
        return [
            ['plainaddress'],
            ['jØrgen@somedomain.com'],
            ['#@%^%#$@#$@#.com'],
            ['@example.com'],
            ['Joe Smith <email@example.com>'],
            ['email.example.com'],
            ['email@example@example.com'],
            ['email@example.com (Joe Smith)'],
            ['email@example'],
            ['“email”@example.com'],
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Field "test123" is not defined by type CustomerInput.
     */
    public function testCreateCustomerIfPassedAttributeDosNotExistsInCustomerInput()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            test123: "123test123"
            email: "{$newEmail}"
            password: "{$currentPassword}"
            is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Required parameters are missing: First Name
     */
    public function testCreateCustomerIfNameEmpty()
    {
        $newEmail = 'customer_created' . rand(1, 2000000) . '@example.com';
        $newFirstname = '';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            email: "{$newEmail}"
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            password: "{$currentPassword}"
          	is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoConfigFixture default_store newsletter/general/active 0
     */
    public function testCreateCustomerSubscribed()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            is_subscribed: true
        }
    ) {
        customer {
            email
            is_subscribed
        }
    }
}
QUERY;

        $response = $this->graphQlMutation($query);

        $this->assertEquals(false, $response['createCustomer']['customer']['is_subscribed']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage A customer with the same email address already exists in an associated website.
     */
    public function testCreateCustomerIfCustomerWithProvidedEmailAlreadyExists()
    {
        $existedEmail = 'customer@example.com';
        $password = 'test123#';
        $firstname = 'John';
        $lastname = 'Smith';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            email: "{$existedEmail}"
            password: "{$password}"
            firstname: "{$firstname}"
            lastname: "{$lastname}"
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
        $this->graphQlMutation($query);
    }

    public function tearDown(): void
    {
        $newEmail = 'new_customer@example.com';
        try {
            $customer = $this->customerRepository->get($newEmail);
        } catch (\Exception $exception) {
            return;
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->customerRepository->delete($customer);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
        parent::tearDown();
    }
}
