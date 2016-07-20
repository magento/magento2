<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\HandlerInterfaceFactory as HandlerConfigFactory;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\HandlerInterface as HandlerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterfaceFactory;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\MessageQueue\Consumer\Config\Data as ConfigData;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
 */
class Config implements ConfigInterface
{
    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var ConsumerConfigItemInterfaceFactory
     */
    private $consumerConfigItemFactory;

    /**
     * @var HandlerConfigFactory
     */
    private $handlerFactory;

    /**
     * Initialize dependencies.
     *
     * @param ConfigData $configData
     * @param ConsumerConfigItemInterfaceFactory $consumerConfigItemFactory
     * @param HandlerConfigFactory $handlerFactory
     */
    public function __construct(
        ConfigData $configData,
        ConsumerConfigItemInterfaceFactory $consumerConfigItemFactory,
        HandlerConfigFactory $handlerFactory
    ) {
        $this->configData = $configData;
        $this->consumerConfigItemFactory = $consumerConfigItemFactory;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumer($name)
    {
        $consumerData = $this->configData->get($name);
        if (!$consumerData) {
            throw new LocalizedException(new Phrase("Consumer '%consumer' is not declared.", ['consumer' => $name]));
        }
        return $this->createConsumerConfigItem($consumerData);
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumers()
    {
        $consumerConfigItems = [];
        foreach ($this->configData->get() as $consumerName => $consumerData) {
            $consumerConfigItems[$consumerName] = $this->createConsumerConfigItem($consumerData);
        }
        return $consumerConfigItems;
    }

    /**
     * Create consumer config item object based on provided consumer data.
     *
     * @param array $consumerData
     * @return ConsumerConfigItemInterface
     */
    private function createConsumerConfigItem($consumerData)
    {
        $handlers = [];
        foreach ($consumerData['handlers'] as $handlerConfigItem) {
            $handlers[] = $this->createHandlerConfigItem($handlerConfigItem);
        }
        return $this->consumerConfigItemFactory->create([
            'name' => $consumerData['name'],
            'connection' => $consumerData['connection'],
            'queue' => $consumerData['queue'],
            'consumerInstance' => $consumerData['consumerInstance'],
            'handlers' => $handlers,
            'maxMessages' => $consumerData['maxMessages']
        ]);
    }

    /**
     * Create consumer handler config item object based on provided handler data.
     *
     * @param string $handler
     * @return HandlerConfigItemInterface
     */
    private function createHandlerConfigItem($handler)
    {
        return $this->handlerFactory->create(['type' => $handler['type'], 'method' => $handler['method']]);
    }
}
