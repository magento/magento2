<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Model\Resource\Subscriber;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Resource\Subscriber\Collection
     */
    protected $_collectionModel;

    protected function setUp()
    {
        $this->_collectionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Resource\Subscriber\Collection');
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testShowCustomerInfo()
    {
        $this->_collectionModel->showCustomerInfo()->load();

        /** @var \Magento\Newsletter\Model\Subscriber[] $subscribers */
        $subscribers = $this->_collectionModel->getItems();
        $this->assertCount(2, $subscribers);
        $subscriber = array_shift($subscribers);
        $this->assertEquals('John', $subscriber->getCustomerFirstname(), $subscriber->getSubscriberEmail());
        $this->assertEquals('Smith', $subscriber->getCustomerLastname(), $subscriber->getSubscriberEmail());
        $subscriber = array_shift($subscribers);
        $this->assertNull($subscriber->getCustomerFirstname(), $subscriber->getSubscriberEmail());
        $this->assertNull($subscriber->getCustomerLastname(), $subscriber->getSubscriberEmail());
    }
}
