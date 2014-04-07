<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
 