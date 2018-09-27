<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for revoke customer token mutation
 */
class RevokeCustomerTokenTest extends GraphQlAbstract
{
    /**
     * Verify customers with valid credentials
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRevokeCustomerTokenValidCredentials()
    {
        $query = <<<QUERY
            mutation {
                revokeCustomerToken
            }
QUERY;

        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()
            ->get(\Magento\Integration\Api\CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);

        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertTrue($response['revokeCustomerToken']);
    }

    /**
     * Verify guest customers
     */
    public function testRevokeCustomerTokenForGuestCustomer()
    {
        $query = <<<QUERY
            mutation {
                revokeCustomerToken
            }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Current customer' . ' ' .
            'does not have access to the resource "customer"'
        );
        $response = $this->graphQlQuery($query, [], '');
    }
}
