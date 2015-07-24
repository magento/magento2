<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Class which creates Producers
 */
class Publisher
{
    /**
     * @var ProducerFactory
     */
    private $producerFactory;

    /**
     * Initialize dependencies.
     *
     * @param ProducerFactory $producerFactory
     */
    public function __construct(
        ProducerFactory $producerFactory
    ) {
        $this->producerFactory = $producerFactory;
    }

    /**
     * Publishes a message on a topic.
     *
     * @param string $topicName
     * @param object $data
     * @return ProducerInterface
     */
    public function publish($topicName, $data)
    {
        $producer = $this->producerFactory->create($topicName);
        $producer->publish($topicName, $data);
    }
}
