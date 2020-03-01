<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

use Magento\TestFramework\Mail\Template\TransportBuilderMock;

/**
 * \Magento\Newsletter\Model\Subscriber tests
 */
class SubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Subscriber
     */
    private $model;

    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Newsletter\Model\Subscriber::class
        );
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoConfigFixture current_store newsletter/subscription/confirm 1
     */
    public function testEmailConfirmation()
    {
        $this->model->subscribe('customer_confirm@example.com');
        /** @var TransportBuilderMock $transportBuilder */
        $transportBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);
        // confirmationCode 'ysayquyajua23iq29gxwu2eax2qb6gvy' is taken from fixture
        $this->assertContains(
            '/newsletter/subscriber/confirm/id/' . $this->model->getSubscriberId()
            . '/code/ysayquyajua23iq29gxwu2eax2qb6gvy',
            $transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
        $this->assertEquals(Subscriber::STATUS_NOT_ACTIVE, $this->model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testLoadByCustomerId()
    {
        $this->assertSame($this->model, $this->model->loadByCustomerId(1));
        $this->assertEquals('customer@example.com', $this->model->getSubscriberEmail());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoAppArea     frontend
     */
    public function testUnsubscribeSubscribe()
    {
        // Unsubscribe and verify
        $this->assertSame($this->model, $this->model->loadByCustomerId(1));
        $this->assertEquals($this->model, $this->model->unsubscribe());
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->model->getSubscriberStatus());

        // Subscribe and verify
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->model->subscribe('customer@example.com'));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoAppArea     frontend
     */
    public function testUnsubscribeSubscribeByCustomerId()
    {
        // Unsubscribe and verify
        $this->assertSame($this->model, $this->model->unsubscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->model->getSubscriberStatus());

        // Subscribe and verify
        $this->assertSame($this->model, $this->model->subscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoConfigFixture current_store newsletter/subscription/confirm 1
     */
    public function testConfirm()
    {
        $customerEmail = 'customer_confirm@example.com';
        $this->model->subscribe($customerEmail);
        $this->model->loadByEmail($customerEmail);
        $this->model->confirm($this->model->getSubscriberConfirmCode());

        $transportBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\TestFramework\Mail\Template\TransportBuilderMock::class
        );

        $this->assertContains(
            'You have been successfully subscribed to our newsletter.',
            $transportBuilder->getSentMessage()->getBody()->getParts()[0]->getRawContent()
        );
    }
}
