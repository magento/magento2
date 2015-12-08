<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Rpc;

use \Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\PublisherFactory;

/**
 * Client class which will publish any message
 */
class PublisherProxy implements PublisherInterface
{
    /**
     * @var PublisherFactory
     */
    private $publisherFactory;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * Initialize dependencies.
     *
     * @param PublisherFactory $publisherFactory
     * @param MessageEncoder $messageEncoder
     */
    public function __construct(
        PublisherFactory $publisherFactory,
        MessageEncoder $messageEncoder
    ) {
        $this->publisherFactory = $publisherFactory;
        $this->messageEncoder = $messageEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $publisher = $this->publisherFactory->create($topicName);
        $message = $this->messageEncoder->encode($topicName, $data);
        $responseMessage = $publisher->publish($topicName, $message);
        return $this->messageEncoder->decode($topicName, $responseMessage, false);
    }
}
