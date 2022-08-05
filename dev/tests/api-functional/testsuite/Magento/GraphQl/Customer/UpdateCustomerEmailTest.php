<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for update customer's email
 */
class UpdateCustomerEmailTest extends GraphQlAbstract
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
     * @var UpdateCustomerAccount
     */
    private $updateCustomerAccount;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Setting up tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->updateCustomerAccount = Bootstrap::getObjectManager()->get(UpdateCustomerAccount::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerEmail(): void
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $newEmail = 'newcustomer@example.com';

        $query = <<<QUERY
mutation {
    updateCustomerEmail(
        email: "{$newEmail}"
        password: "{$currentPassword}"
    ) {
        customer {
            email
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

        $this->assertEquals($newEmail, $response['updateCustomerEmail']['customer']['email']);

/*        $this->updateCustomerAccount->execute(
            $this->customerRepository->get($newEmail),
            ['email' => $currentEmail, 'password' => $currentPassword],
            $this->storeRepository->getById(1)
        );*/
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerEmailIfPasswordIsWrong(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid login or password.');

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $newEmail = 'newcustomer@example.com';
        $wrongPassword = 'wrongpassword';

        $query = <<<QUERY
mutation {
    updateCustomerEmail(
        email: "{$newEmail}"
        password: "{$wrongPassword}"
    ) {
        customer {
            email
        }
    }
}
QUERY;

        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testUpdateEmailIfEmailAlreadyExists()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $existedEmail = 'customer_two@example.com';

        $query = <<<QUERY
mutation {
    updateCustomerEmail(
        email: "{$existedEmail}"
        password: "{$currentPassword}"
    ) {
        customer {
            firstname
        }
    }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * Get customer authorization headers
     *
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
