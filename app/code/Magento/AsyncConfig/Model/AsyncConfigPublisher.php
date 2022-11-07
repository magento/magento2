<?php

namespace Magento\AsyncConfig\Model;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterfaceFactory;

class AsyncConfigPublisher implements \Magento\AsyncConfig\Api\AsyncConfigPublisherInterface
{
    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var AsyncConfigMessageInterfaceFactory
     */
    private $asyncConfigFactory;

    /**
     * @var Json
     */
    private $serializer;


    /**
     * @param AsyncConfigMessageInterfaceFactory $asyncConfigFactory
     * @param PublisherInterface $publisher
     * @param Json $json
     */
    public function __construct(
        AsyncConfigMessageInterfaceFactory $asyncConfigFactory,
        PublisherInterface $publisher,
        Json $json
    ) {
        $this->asyncConfigFactory = $asyncConfigFactory;
        $this->messagePublisher = $publisher;
        $this->serializer = $json;
    }

    public function saveConfigData($configData)
    {
        $asyncConfig = $this->asyncConfigFactory->create();
        $asyncConfig->setConfigData($this->serializer->serialize($configData));
        $this->messagePublisher->publish('async_config.saveConfig', $asyncConfig);
    }
}
