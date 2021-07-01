<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Delete custom tests
 */
class DeleteCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testDeleteCustomer()
    {
        $response = $this->graphQlMutation($this->getMutation(), [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('deleteCustomer', $response);
        $this->assertTrue($response['deleteCustomer']);
    }

    /**
     */
    public function testDeleteCustomerIfUserIsNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlMutation($this->getMutation());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testDeleteCustomerIfAccountIsLocked()
    {
        $this->lockCustomer->execute(1);
        $response = $this->graphQlMutation($this->getMutation(), [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('deleteCustomer', $response);
        $this->assertTrue($response['deleteCustomer']);
    }

    /**
     * Retrieve deleteCustomer mutation
     *
     * @return string
     */
    private function getMutation()
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
