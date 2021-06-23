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

        $mutation = $this->getQuery($email, $password);

        $response = $this->graphQlMutation($mutation);
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertIsArray($response['generateCustomerToken']);
    }

    /**
     * Test customer with invalid data.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @dataProvider dataProviderInvalidCustomerInfo
     * @param string $email
     * @param string $password
     * @param string $message
     */
    public function testGenerateCustomerTokenInvalidData(string $email, string $password, string $message)
    {
        $this->expectException(\Exception::class);

        $mutation = $this->getQuery($email, $password);
        $this->expectExceptionMessage($message);
        $this->graphQlMutation($mutation);
    }

    /**
     * Test customer token regeneration.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRegenerateCustomerToken()
    {
        $email = 'customer@example.com';
        $password = 'password';

        $mutation = $this->getQuery($email, $password);

        $response1 = $this->graphQlMutation($mutation);
        $token1 = $response1['generateCustomerToken']['token'];

        sleep(2);

        $response2 = $this->graphQlMutation($mutation);
        $token2 = $response2['generateCustomerToken']['token'];

        $this->assertNotEquals($token1, $token2, 'Tokens should not be identical!');
    }

    /**
     * @return array
     */
    public function dataProviderInvalidCustomerInfo(): array
    {
        return [
            'invalid_email' => [
                'invalid_email@example.com',
                'password',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'empty_email' => [
                '',
                'password',
                'Specify the "email" value.'
            ],
            'invalid_password' => [
                'customer@example.com',
                'invalid_password',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'empty_password' => [
                'customer@example.com',
                '',
                'Specify the "password" value.'

            ]
        ];
    }

    /**
     * @param string $email
     * @param string $password
     * @return string
     */
    private function getQuery(string $email, string $password) : string
    {
        return <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;
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
