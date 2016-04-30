<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            'Magento\Newsletter\Model\Subscriber'
        );
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
