<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Delete customer tests
 */
class DeleteCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test deleting customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testDeleteCustomer(): void
    {
        $response = $this->graphQlMutation($this->getMutation(), [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('deleteCustomer', $response);
        $this->assertTrue($response['deleteCustomer']);
    }

    /**
     * Test deleting non authorized customer
     */
    public function testDeleteCustomerIfUserIsNotAuthorized(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlMutation($this->getMutation());
    }

    /**
     * Test deleting locked customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/locked_customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testDeleteCustomerIfAccountIsLocked(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later'
        );
        $this->graphQlMutation($this->getMutation(), [], '', $this->getHeaderMap());
    }

    /**
     * Retrieve deleteCustomer mutation
     *
     * @return string
     */
    private function getMutation(): string
    {
        return <<<MUTATION
mutation {
  deleteCustomer
}
MUTATION;
    }

    /**
     * Retrieve customer authorization headers
     *
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
