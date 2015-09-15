<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

class QueueManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\MysqlMq\Model\Observer
     */
    private $observer;

    /**
     * @var \Magento\MysqlMq\Model\QueueManagement
     */
    private $queueManagement;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->observer = $this->objectManager->get('Magento\MysqlMq\Model\Observer');
        $this->queueManagement = $this->objectManager->get('Magento\MysqlMq\Model\QueueManagement');
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages.php
     */
    public function testCleanupMessages()
    {
        /** @var \Magento\MysqlMq\Model\Resource\MessageStatusCollectionFactory $messageStatusCollectionFactory */
        $messageStatusCollectionFactory = $this->objectManager
            ->create('Magento\MysqlMq\Model\Resource\MessageStatusCollectionFactory');

        /** @var \Magento\MysqlMq\Model\Resource\MessageCollectionFactory $messageStatusCollectionFactory */
        $messageCollectionFactory = $this->objectManager
            ->create('Magento\MysqlMq\Model\Resource\MessageCollectionFactory');
        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());
        $messageId = $messageCollection->getFirstItem()->getId();

        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(4, $messageStatusCollection->getSize());

        $this->observer->cleanupMessages();

        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());
        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId)
            ->addFieldToFilter('status', \Magento\MysqlMq\Model\QueueManagement::MESSAGE_STATUS_TO_BE_DELETED);
        $this->assertEquals(3, $messageStatusCollection->getSize());

        $lastMessageStatus = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId)
            ->addFieldToFilter('status', \Magento\MysqlMq\Model\QueueManagement::MESSAGE_STATUS_COMPLETE)
            ->getFirstItem();
        $lastMessageStatus->setUpdatedAt(time() - 24 * 7 * 60 * 60)
            ->save();

        $this->observer->cleanupMessages();

        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(0, $messageCollection->getSize());
        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(0, $messageStatusCollection->getSize());

        //add use case when all the messages are in deleted status (NOT IN doesn't work)
    }
}