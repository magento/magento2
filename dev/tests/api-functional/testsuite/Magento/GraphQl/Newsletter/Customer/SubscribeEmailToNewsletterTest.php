<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Newsletter\Customer;

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
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
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
     * @expectedException Exception
     * @expectedExceptionMessage Enter a valid email address.
     */
    public function testNewsletterSubscriptionWithIncorrectEmailFormat()
    {
        $query = $this->getQuery('customer.example.com');

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Newsletter/_files/subscribers.php
     * @expectedException Exception
     * @expectedExceptionMessage This email address is already subscribed.
     */
    public function testNewsletterSubscriptionWithAlreadySubscribedEmail()
    {
        $query = $this->getQuery('customer@example.com');

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Newsletter/_files/three_subscribers.php
     * @expectedException Exception
     * @expectedExceptionMessage This email address is already assigned to another user.
     */
    public function testNewsletterSubscriptionWithAnotherCustomerEmail()
    {
        $query = $this->getQuery('customer2@search.example.com');

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
    public function tearDown()
    {
        $this->subscriberResource
            ->getConnection()
            ->delete(
                $this->subscriberResource->getMainTable()
            );

        parent::tearDown();
    }
}
