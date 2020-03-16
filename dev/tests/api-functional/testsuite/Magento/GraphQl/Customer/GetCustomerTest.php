<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->customerAuthUpdate = Bootstrap::getObjectManager()->get(CustomerAuthUpdate::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomer()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        id
        firstname
        lastname
        email
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertEquals(null, $response['customer']['id']);
        $this->assertEquals('John', $response['customer']['firstname']);
        $this->assertEquals('Smith', $response['customer']['lastname']);
        $this->assertEquals($currentEmail, $response['customer']['email']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testGetCustomerIfUserIsNotAuthorized()
    {
        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage The account is locked.
     */
    public function testGetCustomerIfAccountIsLocked()
    {
        $this->lockCustomer(1);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer_confirmation_config_enable.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedExceptionMessage This account isn't confirmed. Verify and try again.
     */
    public function testAccountIsNotConfirmed()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $headersMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $customer = $this->customerRepository->getById(1)->setConfirmation(
            \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED
        );
        $this->customerRepository->save($customer);
        $query = <<<QUERY
query {
    customer {
        firstname
        lastname
        email
    }
}
QUERY;
        $this->graphQlQuery($query, [], '', $headersMap);
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * @param int $customerId
     * @return void
     */
    private function lockCustomer(int $customerId): void
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerSecure->setLockExpires('2030-12-31 00:00:00');
        $this->customerAuthUpdate->saveAuth($customerId);
    }
}
