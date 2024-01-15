<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\HttpClient\CurlClient;

/**
 * Test customer authentication responses
 */
class AuthenticationTest extends GraphQlAbstract
{
    private const QUERY_ACCESSIBLE_BY_GUEST = <<<QUERY
{
  isEmailAvailable(email: "customer@example.com") {
    is_email_available
  }
}
QUERY;

    private const QUERY_REQUIRE_AUTHENTICATION = <<<QUERY
{
  customer {
    email
  }
}
QUERY;

    private $tokenService;

    protected function setUp(): void
    {
        $this->tokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    public function testNoToken()
    {
        $response = $this->graphQlQuery(self::QUERY_ACCESSIBLE_BY_GUEST);

        self::assertArrayHasKey('isEmailAvailable', $response);
        self::assertArrayHasKey('is_email_available', $response['isEmailAvailable']);
    }

    public function testInvalidToken()
    {
        $this->expectExceptionCode(401);
        Bootstrap::getObjectManager()->get(CurlClient::class)->get(
            rtrim(TESTS_BASE_URL, '/') . '/graphql',
            [
                'query' => self::QUERY_ACCESSIBLE_BY_GUEST
            ],
            [
                'Authorization: Bearer invalid_token'
            ]
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testRevokedTokenPublicQuery()
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $token = $this->tokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $response = $this->graphQlQuery(
            self::QUERY_ACCESSIBLE_BY_GUEST,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        self::assertArrayHasKey('isEmailAvailable', $response);
        self::assertArrayHasKey('is_email_available', $response['isEmailAvailable']);

        $this->tokenService->revokeCustomerAccessToken($customer->getId());

        $this->expectExceptionCode(401);
        Bootstrap::getObjectManager()->get(CurlClient::class)->get(
            rtrim(TESTS_BASE_URL, '/') . '/graphql',
            [
                'query' => self::QUERY_ACCESSIBLE_BY_GUEST
            ],
            [
                'Authorization: Bearer ' . $token
            ]
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testRevokedTokenProtectedQuery()
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $token = $this->tokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $response = $this->graphQlQuery(
            self::QUERY_REQUIRE_AUTHENTICATION,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        self::assertEquals(
            [
                'customer' => [
                    'email' => $customer->getEmail()
                ]
            ],
            $response
        );

        $this->tokenService->revokeCustomerAccessToken($customer->getId());

        $this->expectExceptionCode(401);
        Bootstrap::getObjectManager()->get(CurlClient::class)->get(
            rtrim(TESTS_BASE_URL, '/') . '/graphql',
            [
                'query' => self::QUERY_REQUIRE_AUTHENTICATION
            ],
            [
                'Authorization: Bearer ' . $token
            ]
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            Customer::class,
            [
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'default_billing' => true,
                        'default_shipping' => true
                    ]
                ]
            ],
            as: 'customer2'
        ),
    ]
    public function testForbidden()
    {
        /** @var CustomerInterface $customer2 */
        $customer2Data = DataFixtureStorageManager::getStorage()->get('customer2');
        $customer2 = Bootstrap::getObjectManager()
            ->get(CustomerRepositoryInterface::class)
            ->get($customer2Data->getEmail());
        $addressId = $customer2->getDefaultBilling();
        $mutation
            = <<<MUTATION
mutation {
  deleteCustomerAddress(id: {$addressId})
}
MUTATION;

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $token = $this->tokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $this->expectExceptionCode(403);
        Bootstrap::getObjectManager()->get(CurlClient::class)->post(
            rtrim(TESTS_BASE_URL, '/') . '/graphql',
            json_encode(['query' => $mutation]),
            [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        );
    }
}
