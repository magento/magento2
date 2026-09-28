<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\Authorization\Test\Fixture\Role;
use Magento\Customer\Test\Fixture\Customer;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Test\Fixture\User;
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }

    #[
        DataFixture(Customer::class, ['firstname' => 'John', 'lastname' => 'Smith'], 'customer')
    ]
    public function testGetCustomer(): void
    {
        $customerEmail = DataFixtureStorageManager::getStorage()->get('customer')->getEmail();
        $this->assertEquals(
            [
                'customer' => [
                    'firstname' => 'John',
                    'lastname' => 'Smith',
                    'email' => $customerEmail
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($customerEmail)
            )
        );
    }

    public function testGetCustomerIfUserIsNotAuthorized(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $this->graphQlQuery($this->getCustomerQuery());
    }

    #[
        DataFixture(Role::class, as: 'role'),
        DataFixture(User::class, ['role_id' => '$role.id$'], 'admin_user')
    ]
    public function testGetCustomerIfUserHasWrongType(): void
    {
        $adminUser = DataFixtureStorageManager::getStorage()->get('admin_user');
        $adminToken = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class)
            ->createAdminAccessToken($adminUser->getUserName(), TestBootstrap::ADMIN_PASSWORD);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $this->graphQlQuery(
            $this->getCustomerQuery(),
            [],
            '',
            ['Authorization' => 'Bearer ' . $adminToken]
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testGetCustomerIfAccountIsLocked(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->lockCustomer->execute((int)$customer->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The account is locked.');

        $this->graphQlQuery(
            $this->getCustomerQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );
    }

    #[
        Config('customer/create_account/confirm', true),
        DataFixture(Customer::class, as: 'customer')
    ]
    public function testAccountIsNotConfirmed(): void
    {
        $this->expectExceptionMessage("This account isn't confirmed. Verify and try again.");
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerEntity = $this->customerRepository->getById((int)$customer->getId())
            ->setConfirmation(AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED);
        $this->customerRepository->save($customerEntity);

        $this->graphQlQuery(
            $this->getCustomerQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );
    }

    /**
     * Get headers with customer authorization token
     *
     * @param string $email
     * @return array
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Get basic customer query
     *
     * @return string
     */
    private function getCustomerQuery(): string
    {
        return <<<QUERY
            query {
                customer {
                    firstname
                    lastname
                    email
                }
            }
        QUERY;
    }
}
