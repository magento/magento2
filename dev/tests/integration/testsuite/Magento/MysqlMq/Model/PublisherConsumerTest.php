<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Model;

use Magento\Framework\MessageQueue\UseCase\QueueTestCaseAbstract;
use Magento\MysqlMq\Model\ResourceModel\MessageCollection;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollection;

/**
 * Test for MySQL publisher class.
 *
 * @magentoDbIsolation disabled
 */
class PublisherConsumerTest extends QueueTestCaseAbstract
{
    const MAX_NUMBER_OF_TRIALS = 3;

    /**
     * @var string[]
     */
    protected $consumers = [
        'demoConsumerQueueOne',
        'demoConsumerQueueTwo',
        'demoConsumerQueueThree',
        'delayedOperationConsumer',
        'demoConsumerWithException'
    ];

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testPublishConsumeFlow()
    {
        /** @var \Magento\TestModuleMysqlMq\Model\DataObjectFactory $objectFactory */
        $objectFactory = $this->objectManager->create(\Magento\TestModuleMysqlMq\Model\DataObjectFactory::class);
        /** @var \Magento\TestModuleMysqlMq\Model\DataObject $object */
        $object = $objectFactory->create();
        $object->setOutputPath($this->logFilePath);
        file_put_contents($this->logFilePath, '');
        for ($i = 0; $i < 10; $i++) {
            $object->setName('Object name ' . $i)->setEntityId($i);
            $this->publisher->publish('demo.object.created', $object);
        }
        for ($i = 0; $i < 5; $i++) {
            $object->setName('Object name ' . $i)->setEntityId($i);
            $this->publisher->publish('demo.object.updated', $object);
        }
        for ($i = 0; $i < 3; $i++) {
            $object->setName('Object name ' . $i)->setEntityId($i);
            $this->publisher->publish('demo.object.custom.created', $object);
        }
        $this->waitForAsynchronousResult(18, $this->logFilePath);

        //Check lines in file
        $createdPattern = '/Processed object created \d+/';
        $updatedPattern = '/Processed object updated \d+/';
        $customCreatedPattern = '/Processed custom object created \d+/';
        $logFileContents = file_get_contents($this->logFilePath);

        preg_match_all($createdPattern, $logFileContents, $createdMatches);
        $this->assertEquals(10, count($createdMatches[0]));
        preg_match_all($updatedPattern, $logFileContents, $updatedMatches);
        $this->assertEquals(5, count($updatedMatches[0]));
        preg_match_all($customCreatedPattern, $logFileContents, $customCreatedMatches);
        $this->assertEquals(3, count($customCreatedMatches[0]));
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testPublishAndConsumeSchemaDefinedByMethod()
    {
        $topic = 'test.schema.defined.by.method';
        /** @var \Magento\TestModuleMysqlMq\Model\DataObjectFactory $objectFactory */
        $objectFactory = $this->objectManager->create(\Magento\TestModuleMysqlMq\Model\DataObjectFactory::class);
        /** @var \Magento\TestModuleMysqlMq\Model\DataObject $object */
        $object = $objectFactory->create();
        $id = 33;
        $object->setName('Object name ' . $id)->setEntityId($id);
        $object->setOutputPath($this->logFilePath);
        $requiredStringParam = 'Required value';
        $optionalIntParam = 44;
        $this->publisher->publish($topic, [$object, $requiredStringParam, $optionalIntParam]);

        $expectedOutput = "Processed '{$object->getEntityId()}'; "
            . "Required param '{$requiredStringParam}'; Optional param '{$optionalIntParam}'";

        $this->waitForAsynchronousResult(1, $this->logFilePath);

        $this->assertEquals($expectedOutput, trim(file_get_contents($this->logFilePath)));
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testConsumeWithException()
    {
        $topic = 'demo.exception';
        /** @var \Magento\TestModuleMysqlMq\Model\DataObjectFactory $objectFactory */
        $objectFactory = $this->objectManager->create(\Magento\TestModuleMysqlMq\Model\DataObjectFactory::class);
        /** @var \Magento\TestModuleMysqlMq\Model\DataObject $object */
        $object = $objectFactory->create();
        $id = 99;

        $object->setName('Object name ' . $id)->setEntityId($id);
        $object->setOutputPath($this->logFilePath);
        $this->publisher->publish($topic, $object);
        $expectedOutput = "Exception processing {$id}";
        $this->waitForAsynchronousResult(1, $this->logFilePath);
        $message = $this->getTopicLatestMessage($topic);
        $this->assertEquals($expectedOutput, trim(file_get_contents($this->logFilePath)));
        $this->assertEquals(QueueManagement::MESSAGE_STATUS_ERROR, $message->getStatus());
    }

    /**
     * @param string $topic
     * @return Message
     */
    private function getTopicLatestMessage(string $topic) : Message
    {
        // Assert message status is error
        $messageCollection = $this->objectManager->create(MessageCollection::class);
        $messageStatusCollection = $this->objectManager->create(MessageStatusCollection::class);

        $messageCollection->addFilter('topic_name', $topic);
        $messageCollection->join(
            ['status' => $messageStatusCollection->getMainTable()],
            "status.message_id = main_table.id"
        );
        $messageCollection->addOrder('updated_at', MessageCollection::SORT_ORDER_DESC);

        $message = $messageCollection->getFirstItem();
        return $message;
    }
}
