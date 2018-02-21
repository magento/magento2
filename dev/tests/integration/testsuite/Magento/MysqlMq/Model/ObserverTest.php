<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

class ObserverTest extends \PHPUnit\Framework\TestCase
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
        $this->observer = $this->objectManager->get(\Magento\MysqlMq\Model\Observer::class);
        $this->queueManagement = $this->objectManager->get(\Magento\MysqlMq\Model\QueueManagement::class);
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages_done_old.php
     */
    public function testCleanUpOld()
    {
        /** @var \Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory $messageStatusCollectionFactory */
        $messageStatusCollectionFactory = $this->objectManager
            ->create(\Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory::class);

        /** @var \Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory $messageStatusCollectionFactory */
        $messageCollectionFactory = $this->objectManager
            ->create(\Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory::class);

        //Check how many messages in collection by the beginning of tests
        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());
        $messageId = $messageCollection->getFirstItem()->getId();

        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(3, $messageStatusCollection->getSize());

        //Run clean up once. It should move 3 out of 4 statuses to TO BE DELETED status
        $this->observer->cleanupMessages();

        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(0, $messageCollection->getSize());
        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(0, $messageStatusCollection->getSize());
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages_done_old.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages_done_recent.php
     */
    public function testCleanupMessages()
    {
        /** @var \Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory $messageStatusCollectionFactory */
        $messageStatusCollectionFactory = $this->objectManager
            ->create(\Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory::class);

        /** @var \Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory $messageStatusCollectionFactory */
        $messageCollectionFactory = $this->objectManager
            ->create(\Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory::class);

        //Check how many messages in collection by the beginning of tests
        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());
        $messageId = $messageCollection->getFirstItem()->getId();

        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(4, $messageStatusCollection->getSize());

        //Run clean up once. It should move 3 out of 4 statuses to TO BE DELETED status
        $this->observer->cleanupMessages();

        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());

        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId)
            ->addFieldToFilter('status', \Magento\MysqlMq\Model\QueueManagement::MESSAGE_STATUS_TO_BE_DELETED);

        $this->assertEquals(3, $messageStatusCollection->getSize());

        // Change the Updated At in order to make job visible
        $lastMessageStatus = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId)
            ->addFieldToFilter('status', \Magento\MysqlMq\Model\QueueManagement::MESSAGE_STATUS_COMPLETE)
            ->getFirstItem();
        $lastMessageStatus->setUpdatedAt(time() - 1 - 24 * 7 * 60 * 60)
            ->save();

        $this->observer->cleanupMessages();

        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic.updated.use.just.in.tests');
        $this->assertEquals(0, $messageCollection->getSize());
        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(0, $messageStatusCollection->getSize());
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages.php
     * @magentoDataFixture Magento/MysqlMq/_files/messages_in_progress.php
     */
    public function testCleanupInProgressMessages()
    {
        /** @var \Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory $messageStatusCollectionFactory */
        $messageStatusCollectionFactory = $this->objectManager
            ->create(\Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory::class);

        /** @var \Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory $messageStatusCollectionFactory */
        $messageCollectionFactory = $this->objectManager
            ->create(\Magento\MysqlMq\Model\ResourceModel\MessageCollectionFactory::class);

        //Check how many messages in collection by the beginning of tests
        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic_second.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());
        $messageId = $messageCollection->getFirstItem()->getId();

        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId);
        $this->assertEquals(2, $messageStatusCollection->getSize());

        $this->observer->cleanupMessages();

        $messageCollection = $messageCollectionFactory->create()
            ->addFieldToFilter('topic_name', 'topic_second.updated.use.just.in.tests');
        $this->assertEquals(1, $messageCollection->getSize());
        $messageStatusCollection = $messageStatusCollectionFactory->create()
            ->addFieldToFilter('message_id', $messageId)
            ->addFieldToFilter('status', \Magento\MysqlMq\Model\QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED);
        $this->assertEquals(1, $messageStatusCollection->getSize());
        $this->assertEquals(1, $messageStatusCollection->getFirstItem()->getNumberOfTrials());
    }
}
