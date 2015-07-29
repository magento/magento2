<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Client class which will publish any message
 */
class Publisher
{
    /**
     * @var ProducerFactory
     */
    private $producerFactory;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * Initialize dependencies.
     *
     * @param ProducerFactory $producerFactory
     * @param MessageEncoder $messageEncoder
     */
    public function __construct(
        ProducerFactory $producerFactory,
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
        // $message = $this->messageEncoder->encode($data, $topicName);
        $producer->publish($topicName, $data);
    }
}
