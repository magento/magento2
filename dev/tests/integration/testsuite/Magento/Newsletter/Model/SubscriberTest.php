<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Model;

class SubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Subscriber
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Newsletter\Model\Subscriber::class
        );
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoConfigFixture current_store newsletter/subscription/confirm 1
     */
    public function testEmailConfirmation()
    {
        $this->_model->subscribe('customer_confirm@example.com');
        $transportBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);
        // confirmationCode 'ysayquyajua23iq29gxwu2eax2qb6gvy' is taken from fixture
        $this->assertContains(
            '/newsletter/subscriber/confirm/id/' . $this->_model->getSubscriberId()
            . '/code/ysayquyajua23iq29gxwu2eax2qb6gvy',
            $transportBuilder->getSentMessage()->getBodyHtml()->getRawContent()
        );
        $this->assertEquals(Subscriber::STATUS_NOT_ACTIVE, $this->_model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testLoadByCustomerId()
    {
        $this->assertSame($this->_model, $this->_model->loadByCustomerId(1));
        $this->assertEquals('customer@example.com', $this->_model->getSubscriberEmail());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoAppArea     frontend
     */
    public function testUnsubscribeSubscribe()
    {
        // Unsubscribe and verify
        $this->assertSame($this->_model, $this->_model->loadByCustomerId(1));
        $this->assertEquals($this->_model, $this->_model->unsubscribe());
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->_model->getSubscriberStatus());

        // Subscribe and verify
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->_model->subscribe('customer@example.com'));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->_model->getSubscriberStatus());
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoAppArea     frontend
     */
    public function testUnsubscribeSubscribeByCustomerId()
    {
        // Unsubscribe and verify
        $this->assertSame($this->_model, $this->_model->unsubscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_UNSUBSCRIBED, $this->_model->getSubscriberStatus());

        // Subscribe and verify
        $this->assertSame($this->_model, $this->_model->subscribeCustomerById(1));
        $this->assertEquals(Subscriber::STATUS_SUBSCRIBED, $this->_model->getSubscriberStatus());
    }
}
