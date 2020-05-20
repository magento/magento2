<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Newsletter\Customer;

use Exception;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test newsletter email subscription for customer
 */
class SubscribeEmailToNewsletterTest extends GraphQlAbstract
{
    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var SubscriberResourceModel
     */
    private $subscriberResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthUpdate = Bootstrap::getObjectManager()->get(CustomerAuthUpdate::class);
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->subscriberResource = $objectManager->get(SubscriberResourceModel::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddRegisteredCustomerEmailIntoNewsletterSubscription()
    {
        $query = $this->getQuery('customer@example.com');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('subscribeEmailToNewsletter', $response);
        self::assertNotEmpty($response['subscribeEmailToNewsletter']);
        self::assertEquals('SUBSCRIBED', $response['subscribeEmailToNewsletter']['status']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddLockedCustomerEmailIntoNewsletterSubscription()
    {
        /* lock customer */
        $customerSecure = $this->customerRegistry->retrieveSecureData(1);
        $customerSecure->setLockExpires('2030-12-31 00:00:00');
        $this->customerAuthUpdate->saveAuth(1);

        $query = $this->getQuery('customer@example.com');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('subscribeEmailToNewsletter', $response);
        self::assertNotEmpty($response['subscribeEmailToNewsletter']);
        self::assertEquals('SUBSCRIBED', $response['subscribeEmailToNewsletter']['status']);
    }

    /**
     * @magentoConfigFixture default_store newsletter/subscription/confirm 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testSubscribeRegisteredCustomerEmailWithEnabledConfirmation()
    {
        $query = $this->getQuery('customer@example.com');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('subscribeEmailToNewsletter', $response);
        self::assertNotEmpty($response['subscribeEmailToNewsletter']);
        self::assertEquals('NOT_ACTIVE', $response['subscribeEmailToNewsletter']['status']);
    }

    /**
     * @magentoConfigFixture default_store customer/create_account/confirm 1
     * @magentoApiDataFixture Magento/Customer/_files/unconfirmed_customer.php
     * @expectedException Exception
     * @expectedExceptionMessage The account sign-in was incorrect or your account is disabled temporarily.
     *  Please wait and try again later
     */
    public function testNewsletterSubscriptionWithUnconfirmedCustomer()
    {
        $headers = $this->getHeaderMap('unconfirmedcustomer@example.com', 'Qwert12345');
        $query = $this->getQuery('unconfirmedcustomer@example.com');

        $this->graphQlMutation($query, [], '', $headers);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testNewsletterSubscriptionWithIncorrectEmailFormat()
    {
        $query = $this->getQuery('customer.example.com');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Enter a valid email address.' . "\n");

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testNewsletterSubscriptionWithAlreadySubscribedEmail()
    {
        $query = $this->getQuery('customer@example.com');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This email address is already subscribed.' . "\n");

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Newsletter/_files/three_subscribers.php
     */
    public function testNewsletterSubscriptionWithAnotherCustomerEmail()
    {
        $query = $this->getQuery('customer2@search.example.com');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot create a newsletter subscription.' . "\n");

        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer@search.example.com'));
    }

    /**
     * Returns a mutation query
     *
     * @param string $email
     * @return string
     */
    private function getQuery(string $email = ''): string
    {
        return <<<QUERY
mutation {
  subscribeEmailToNewsletter(
    email: "$email"
  ) {
    status
  }
}
QUERY;
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

        return [
            'Authorization' => 'Bearer ' . $customerToken
        ];
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        $this->subscriberResource
            ->getConnection()
            ->delete(
                $this->subscriberResource->getMainTable()
            );

        parent::tearDown();
    }
}
