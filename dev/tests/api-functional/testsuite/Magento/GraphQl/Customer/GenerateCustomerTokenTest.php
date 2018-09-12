<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class GenerateCustomerTokenTest extends GraphQlAbstract
{

    /**
     * Verify customer token with valid credentials
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
    )
}
MUTATION;

        $response = $this->graphQlQuery($mutation);
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertInternalType('string', $response['generateCustomerToken']);
    }

    /**
     * Verify customer with invalid credentials
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
    )
}
MUTATION;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The account sign-in' . ' ' .
            'was incorrect or your account is disabled temporarily. Please wait and try again later.');
        $this->graphQlQuery($mutation);
    }
}
