<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for @see \Magento\CustomerGraphQl\Model\Customer\GetCustomer.
 */
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

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->customerAuthUpdate = $this->objectManager->get(CustomerAuthUpdate::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
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

        $this->assertNull($response['customer']['id']);
        $this->assertEquals('John', $response['customer']['firstname']);
        $this->assertEquals('Smith', $response['customer']['lastname']);
        $this->assertEquals($currentEmail, $response['customer']['email']);
    }

    /**
     */
    public function testGetCustomerIfUserIsNotAuthorized()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

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
     * @magentoApiDataFixture Magento/User/_files/user_with_role.php
     * @return void
     */
    public function testGetCustomerIfUserHasWrongType(): void
    {
        /** @var $adminTokenService AdminTokenServiceInterface */
        $adminTokenService = $this->objectManager->get(AdminTokenServiceInterface::class);
        $adminToken = $adminTokenService->createAdminAccessToken('adminUser', TestBootstrap::ADMIN_PASSWORD);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

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
            ['Authorization' => 'Bearer ' . $adminToken]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerIfAccountIsLocked()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $customer = $this->customerRepository->get($currentEmail);

        $this->lockCustomer((int)$customer->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The account is locked.');

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
     * @magentoConfigFixture customer/create_account/confirm 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testAccountIsNotConfirmed()
    {
        $this->expectExceptionMessage("This account isn't confirmed. Verify and try again.");
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $customer = $this->customerRepository->get($customerEmail);
        $headersMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $customer = $this->customerRepository->getById((int)$customer->getId())
            ->setConfirmation(AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED);
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
