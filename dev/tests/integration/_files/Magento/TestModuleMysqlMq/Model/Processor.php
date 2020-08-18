<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMysqlMq\Model;

/**
 * Test message processor is used by \Magento\MysqlMq\Model\PublisherConsumerTest
 */
class Processor
{
    /**
     * @param \Magento\TestModuleMysqlMq\Model\DataObject $message
     */
    public function processMessage($message)
    {
        file_put_contents(
            $message->getOutputPath(),
            "Processed {$message->getEntityId()}" . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param \Magento\TestModuleMysqlMq\Model\DataObject $message
     */
    public function processObjectCreated($message)
    {
        file_put_contents(
            $message->getOutputPath(),
            "Processed object created {$message->getEntityId()}" . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param \Magento\TestModuleMysqlMq\Model\DataObject $message
     */
    public function processCustomObjectCreated($message)
    {
        file_put_contents(
            $message->getOutputPath(),
            "Processed custom object created {$message->getEntityId()}" . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param \Magento\TestModuleMysqlMq\Model\DataObject $message
     */
    public function processObjectUpdated($message)
    {
        file_put_contents(
            $message->getOutputPath(),
            "Processed object updated {$message->getEntityId()}" . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param \Magento\TestModuleMysqlMq\Model\DataObject $message
     */
    public function processMessageWithException($message)
    {
        file_put_contents($message->getOutputPath(), "Exception processing {$message->getEntityId()}");
        throw new \LogicException(
            "Exception during message processing happened. Entity: {{$message->getEntityId()}}"
        );
    }
}
