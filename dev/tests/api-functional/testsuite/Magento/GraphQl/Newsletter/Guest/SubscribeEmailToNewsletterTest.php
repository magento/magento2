<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Newsletter\Guest;

use Magento\Newsletter\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test newsletter email subscription for guest
 */
class SubscribeEmailToNewsletterTest extends GraphQlAbstract
{
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
        $this->subscriberResource = $objectManager->get(SubscriberResourceModel::class);
    }

    public function testAddEmailIntoNewsletterSubscription()
    {
        $query = $this->getQuery('guest@example.com');
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('subscribeEmailToNewsletter', $response);
        self::assertNotEmpty($response['subscribeEmailToNewsletter']);
        self::assertEquals('SUBSCRIBED', $response['subscribeEmailToNewsletter']['status']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Enter a valid email address.
     */
    public function testNewsletterSubscriptionWithIncorrectEmailFormat()
    {
        $query = $this->getQuery('guest.example.com');

        $this->graphQlMutation($query);
    }

    /**
     * @magentoConfigFixture default_store newsletter/subscription/allow_guest_subscribe 0
     * @expectedException Exception
     * @expectedExceptionMessage Guests can not subscribe to the newsletter. You must create an account to subscribe.
     */
    public function testNewsletterSubscriptionWithDisallowedGuestSubscription()
    {
        $query = $this->getQuery('guest@example.com');

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Newsletter/_files/guest_subscriber.php
     * @expectedException Exception
     * @expectedExceptionMessage This email address is already subscribed.
     */
    public function testNewsletterSubscriptionWithAlreadySubscribedEmail()
    {
        $query = $this->getQuery('guest@example.com');

        $this->graphQlMutation($query);
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
