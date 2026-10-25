<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixture;

/**
 * Tests for confirmation status
 */
class ConfirmationStatusTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    #[
        Config('customer/create_account/confirm', 0),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
            ],
            'customer'
        )
    ]
    public function testGetConfirmationStatusConfirmationNotRequiredTest()
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $query = <<<QUERY
query {
    customer {
        confirmation_status
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap($customer->getEmail(), 'password')
        );
        $this->assertEquals(
            strtoupper(AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED),
            $response['customer']['confirmation_status']
        );
    }

    #[
        Config('customer/create_account/confirm', 1),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
            ],
            'customer'
        )
    ]
    public function testGetConfirmationStatusConfirmedTest()
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $query = <<<QUERY
query {
    customer {
        confirmation_status
    }
}
QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getHeaderMap($customer->getEmail(), 'password')
        );
        $this->assertEquals(
            strtoupper(AccountManagementInterface::ACCOUNT_CONFIRMED),
            $response['customer']['confirmation_status']
        );
    }

    #[
        Config('customer/create_account/confirm', 1),
        DataFixture(
            Customer::class,
            [
                'email' => 'another@example.com',
            ],
            'customer'
        )
    ]
    public function testGetConfirmationStatusConfirmationRequiredTest()
    {
        $this->expectExceptionMessage("This account isn't confirmed. Verify and try again.");
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $headersMap = $this->getHeaderMap($customer->getEmail(), 'password');
        $customer->setConfirmation(AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED);
        $this->customerRepository->save($this->customerRepository->get($customer->getEmail()));
        $query = <<<QUERY
query {
    customer {
        confirmation_status
    }
}
QUERY;
        $this->graphQlQuery(
            $query,
            [],
            '',
            $headersMap
        );
    }

    #[
        DataFixture(
            Customer::class,
            [
                'email' => 'test@example.com',
            ],
            'customer'
        )
    ]
    public function testGetConfirmationStatusIfUserIsNotAuthorizedTest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
query {
    customer {
        confirmation_status
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
