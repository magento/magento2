<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\ConsumerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Amqp\MessageEncoder;
use Magento\Framework\Amqp\ConsumerConfigurationInterface;
use Magento\MysqlMq\Model\QueueManagement;

/**
 * Consumer for MySQL based queue.
 */
class Consumer implements ConsumerInterface
{
    /**
     * @var QueueManagement
     */
    private $queueManagement;

    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * Initialize dependencies.
     *
     * @param QueueManagement $queueManagement
     * @param AmqpConfig $amqpConfig
     * @param MessageEncoder $messageEncoder
     */
    public function __construct(
        QueueManagement $queueManagement,
        AmqpConfig $amqpConfig,
        MessageEncoder $messageEncoder
    ) {
        $this->queueManagement = $queueManagement;
        $this->amqpConfig = $amqpConfig;
        $this->messageEncoder = $messageEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ConsumerConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null, $daemonMode = false)
    {
        $queueName = $this->configuration->getQueueName();
        if ($daemonMode && !isset($maxNumberOfMessages)) {
            throw new LocalizedException(__('Daemon mode is not supported by MySQL queue implementation.'));
        } else {
            $this->run($queueName, $maxNumberOfMessages);
        }
    }

    /**
     * Decode message and invoke callback method
     *
     * @param string $topicName
     * @param string $messageBody
     * @return void
     */
    public function dispatchMessage($topicName, $messageBody)
    {
        $callback = $this->configuration->getCallback();
        try {
            $decodedMessage = $this->messageEncoder->decode($topicName, $messageBody);
            if (isset($decodedMessage)) {
                call_user_func($callback, $decodedMessage);
            }
        } catch (\Exception $e) {
            // TODO: Push message back to queue with appropriate status
        }
    }

    /**
     * Run short running process
     *
     * @param string $queueName
     * @param int $maxNumberOfMessages
     * @return void
     */
    private function run($queueName, $maxNumberOfMessages)
    {
        $maxNumberOfMessages = $maxNumberOfMessages
            ? $maxNumberOfMessages
            : $this->configuration->getMaxMessages() ?: 1;

        $messages = $this->queueManagement->readMessages($queueName, $maxNumberOfMessages);
        foreach ($messages as $message) {
            $this->dispatchMessage($message[QueueManagement::MESSAGE_TOPIC], $message[QueueManagement::MESSAGE_BODY]);
        }
    }
}