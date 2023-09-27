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
 * Tests for create customer (V2)
 */
class CreateCustomerV2Test extends GraphQlAbstract
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
     * @magentoConfigFixture default_store newsletter/general/active 1
     * @dataProvider validEmailAddressDataProvider
     * @throws \Exception
     */
    public function testCreateCustomerAccountWithPassword(string $email)
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';

        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$email}"
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

        $this->assertNull($response['createCustomerV2']['customer']['id']);
        $this->assertEquals($newFirstname, $response['createCustomerV2']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomerV2']['customer']['lastname']);
        $this->assertEquals($email, $response['createCustomerV2']['customer']['email']);
        $this->assertTrue($response['createCustomerV2']['customer']['is_subscribed']);
    }

    /**
     * @return array
     */
    public function validEmailAddressDataProvider(): array
    {
        return [
            ['new_customer@example.com'],
            ['jØrgenV2@somedomain.com'],
            ['“emailV2”@example.com']
        ];
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
    createCustomerV2(
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

        $this->assertEquals($newFirstname, $response['createCustomerV2']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomerV2']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomerV2']['customer']['email']);
        $this->assertTrue($response['createCustomerV2']['customer']['is_subscribed']);
    }

    /**
     */
    public function testCreateCustomerIfInputDataIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomerCreateInput.email of required type String! was not provided.');

        $query = <<<QUERY
mutation {
    createCustomerV2(
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
     */
    public function testCreateCustomerIfEmailMissed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field CustomerCreateInput.email of required type String! was not provided');

        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';

        $query = <<<QUERY
mutation {
    createCustomerV2(
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
    createCustomerV2(
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
            ['#@%^%#$@#$@#.com'],
            ['@example.com'],
            ['Joe Smith <email@example.com>'],
            ['email.example.com'],
            ['email@example@example.com'],
            ['email@example.com (Joe Smith)'],
            ['email@example']
        ];
    }

    /**
     */
    public function testCreateCustomerIfPassedAttributeDosNotExistsInCustomerInput()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field "test123" is not defined by type "CustomerCreateInput".');

        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomerV2(
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
     */
    public function testCreateCustomerIfNameEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameters are missing: First Name');

        $newEmail = 'customer_created' . rand(1, 2000000) . '@example.com';
        $newFirstname = '';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $query = <<<QUERY
mutation {
    createCustomerV2(
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
    createCustomerV2(
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

        $this->assertFalse($response['createCustomerV2']['customer']['is_subscribed']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateCustomerIfCustomerWithProvidedEmailAlreadyExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );

        $existedEmail = 'customer@example.com';
        $password = 'test123#';
        $firstname = 'John';
        $lastname = 'Smith';

        $query = <<<QUERY
mutation {
    createCustomerV2(
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

    protected function tearDown(): void
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
