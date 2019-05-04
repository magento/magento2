<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use PHPUnit\Framework\TestResult;

/**
 * Class GenerateCustomerTokenTest
 * @package Magento\GraphQl\Customer
 */
class GenerateCustomerTokenTest extends GraphQlAbstract
{
    /**
     * Verify customer token with valid credentials
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGenerateCustomerValidToken()
    {
        $userName = 'customer@example.com';
        $password = 'password';

        $mutation
            = <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$userName}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;

        $response = $this->graphQlMutation($mutation);
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertInternalType('array', $response['generateCustomerToken']);
    }

    /**
     * Verify customer with invalid credentials
     */
    public function testGenerateCustomerTokenWithInvalidCredentials()
    {
        $userName = 'customer@example.com';
        $password = 'bad-password';

        $mutation
            = <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$userName}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The account sign-in' . ' ' .
            'was incorrect or your account is disabled temporarily. Please wait and try again later.');
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customer with empty email
     */
    public function testGenerateCustomerTokenWithEmptyEmail()
    {
        $email = '';
        $password = 'bad-password';

        $mutation
            = <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Specify the "email" value.');
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customer with empty password
     */
    public function testGenerateCustomerTokenWithEmptyPassword()
    {
        $email = 'customer@example.com';
        $password = '';

        $mutation
            = <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Specify the "password" value.');
        $this->graphQlMutation($mutation);
    }
}
