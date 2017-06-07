<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Model\ResourceModel\Subscriber;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
     */
    protected $_collectionModel;

    protected function setUp()
    {
        $this->_collectionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Newsletter\Model\ResourceModel\Subscriber\Collection::class);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testShowCustomerInfo()
    {
        $this->_collectionModel->showCustomerInfo()->load();

        /** @var \Magento\Newsletter\Model\Subscriber[] $subscribers */
        $subscribers = $this->_collectionModel->getItems();
        $this->assertCount(3, $subscribers);

        while ($subscribers) {
            $subscriber = array_shift($subscribers);
            if ($subscriber->getCustomerId()) {
                $this->assertEquals('John', $subscriber->getFirstname(), $subscriber->getSubscriberEmail());
                $this->assertEquals('Smith', $subscriber->getLastname(), $subscriber->getSubscriberEmail());
            } else {
                $this->assertNull($subscriber->getFirstname(), $subscriber->getSubscriberEmail());
                $this->assertNull($subscriber->getLastname(), $subscriber->getSubscriberEmail());
            }
        }
    }
}
