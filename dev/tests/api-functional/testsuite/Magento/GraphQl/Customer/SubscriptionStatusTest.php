<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for subscription status
 */
class SubscriptionStatusTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->subscriberFactory = Bootstrap::getObjectManager()->get(SubscriberFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetSubscriptionStatusTest()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
query {
    customer {
        is_subscribed
    }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap($currentEmail, $currentPassword));
        $this->assertFalse($response['customer']['is_subscribed']);
    }

    /**
     */
    public function testGetSubscriptionStatusIfUserIsNotAuthorizedTest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
query {
    customer {
        is_subscribed
    }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testSubscribeCustomer()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            is_subscribed: true
        }
    ) {
        customer {
            is_subscribed
        }
    }
}
QUERY;
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getHeaderMap($currentEmail, $currentPassword)
        );
        $this->assertTrue($response['updateCustomer']['customer']['is_subscribed']);
    }

    /**
     */
    public function testChangeSubscriptionStatuIfUserIsNotAuthorizedTest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            is_subscribed: true
        }
    ) {
        customer {
            is_subscribed
        }
    }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testUnsubscribeCustomer()
    {
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $query = <<<QUERY
mutation {
    updateCustomer(
        input: {
            is_subscribed: false
        }
    ) {
        customer {
            is_subscribed
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap($currentEmail, $currentPassword));
        $this->assertFalse($response['updateCustomer']['customer']['is_subscribed']);
    }

    /**
     * @magentoConfigFixture default_store customer/account_share/scope 0
     * @magentoApiDataFixture Magento/Customer/_files/customer_for_second_website_with_address.php
     */
    public function testSubscriptionStatusInMultiWebsiteSetup(): void
    {
        $currentEmail = 'customer_second_ws_with_addr@example.com';
        $currentPassword = 'Apassword1';

        $query = <<<QUERY
            mutation {
                updateCustomer(
                    input: {
                        is_subscribed: true
                    }
                ) {
                    customer {
                        is_subscribed
                    }
                }
            }
        QUERY;
        $headers = [
            'Store' => 'default',
            'Authorization' => sprintf(
                'Bearer %s',
                $this->customerTokenService->createCustomerAccessToken($currentEmail, $currentPassword)
            ),
        ];
        $response = $this->graphQlMutation($query, [], '', $headers);
        $this->assertTrue($response['updateCustomer']['customer']['is_subscribed']);
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->subscriberFactory->create()->loadByCustomerId(1)->delete();
    }
}
