<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\ApiFunctional;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
     * @dataProvider validEmailAddressDataProvider
     */
    public function testCreateCustomerWithValidEmail(string $email)
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
        $response = $this->graphQlMutation($query);

        $this->assertEquals($email, $response['createCustomer']['customer']['email']);
    }

    /**
     * @dataProvider invalidEmailAddressDataProvider
     */
    public function testCreateCustomerIfEmailIsNotValid($email)
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
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('GraphQL response contains errors: "' . $email . '" is not a valid email address.');

        $response = $this->graphQlMutation($query);
    }

    /**
     * @return array
     */
    public function validEmailAddressDataProvider(): array
    {
        return [
            ['jdoe@site.com'],
            ['email@example.com'],
            ['firstname.lastname@example.com'],
            ['email@subdomain.example.com'],
            ['firstname+lastname@example.com'],
            ['1234567890@example.com'],
            ['email@example-one.com'],
            ['_______@example.com'],
            ['email@example.name'],
            ['email@example.museum'],
            ['email@example.co.jp'],
            ['firstname-lastname@example.com'],
        ];
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
}
