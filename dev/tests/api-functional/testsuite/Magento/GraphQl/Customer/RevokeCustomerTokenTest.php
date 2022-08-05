<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for revoke customer token mutation
 */
class RevokeCustomerTokenTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRevokeCustomerTokenValidCredentials()
    {
        $query = <<<QUERY
            mutation {
                revokeCustomerToken {
                    result
                }
            }
QUERY;

        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);

        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        $this->assertTrue($response['revokeCustomerToken']['result']);
    }

    /**
     */
    public function testRevokeCustomerTokenForGuestCustomer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
            mutation {
                revokeCustomerToken {
                    result
                }
            }
QUERY;
        $this->graphQlMutation($query, [], '');
    }
}
