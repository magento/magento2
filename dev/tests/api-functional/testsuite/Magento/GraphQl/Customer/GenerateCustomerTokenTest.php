<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * API-functional tests cases for generateCustomerToken mutation
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
        $email = 'customer@example.com';
        $password = 'password';

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

        $response = $this->graphQlMutation($mutation);
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertInternalType('array', $response['generateCustomerToken']);
    }

    /**
     * Verify customer with invalid email
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage GraphQL response contains errors: The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.
     */
    public function testGenerateCustomerTokenWithInvalidEmail()
    {
        $email = 'customer@example';
        $password = 'password';

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
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customer with empty email
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGenerateCustomerTokenWithEmptyEmail()
    {
        $email = '';
        $password = 'password';

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
     * Verify customer with invalid credentials
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage GraphQL response contains errors: The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.
     */
    public function testGenerateCustomerTokenWithIncorrectPassword()
    {
        $email = 'customer@example.com';
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

        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customer with empty password
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGenerateCustomerTokenWithInvalidPassword()
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

    /**
     * Verify customer with empty password
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRegenerateCustomerToken()
    {
        $email = 'customer@example.com';
        $password = 'password';

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

        $response1 = $this->graphQlMutation($mutation);
        $token1 = $response1['generateCustomerToken']['token'];

        $response2 = $this->graphQlMutation($mutation);
        $token2 = $response2['generateCustomerToken']['token'];

        $this->assertNotEquals($token1, $token2, 'Tokens should not be identical!');
    }
}
