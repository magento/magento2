<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdminNotification\Model\ResourceModel\Inbox\Collection;

class CriticalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical::class
        );
    }

    /**
     * @magentoDataFixture Magento/AdminNotification/_files/notifications.php
     */
    public function testCollectionContainsLastUnreadCriticalItem()
    {
        $items = array_values($this->_model->getItems());
        $this->assertEquals('Unread Critical 3', $items[0]->getTitle());
    }
}
