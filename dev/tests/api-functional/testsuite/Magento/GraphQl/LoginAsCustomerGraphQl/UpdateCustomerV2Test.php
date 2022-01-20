<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\LoginAsCustomerGraphQl;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for update customer (V2) with allow_remote_shopping_assistance input/output
 */
class UpdateCustomerV2Test extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomer(): void
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomerV2(
        input: {
            allow_remote_shopping_assistance: true
        }
    ) {
        customer {
            allow_remote_shopping_assistance
        }
    }
}
QUERY;
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertTrue($response['updateCustomerV2']['customer']['allow_remote_shopping_assistance']);
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
