<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Client class which will publish any message
 */
class PublisherProxy implements PublisherInterface
{
    /**
     * @var PublisherFactory
     */
    private $producerFactory;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * Initialize dependencies.
     *
     * @param PublisherFactory $producerFactory
     * @param MessageEncoder $messageEncoder
     */
    public function __construct(
        PublisherFactory $producerFactory,
        MessageEncoder $messageEncoder
    ) {
        $this->producerFactory = $producerFactory;
        $this->messageEncoder = $messageEncoder;
    }

    /**
     * Publishes a message on a topic.
     *
     * @param string $topicName
     * @param array|object $data
     * @return void
     */
    public function publish($topicName, $data)
    {
        $producer = $this->producerFactory->create($topicName);
        $message = $this->messageEncoder->encode($topicName, $data);
        $producer->publish($topicName, $message);
    }
}
