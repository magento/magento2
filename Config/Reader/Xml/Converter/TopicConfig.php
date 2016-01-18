<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfigInterface;

/**
 * Converts MessageQueue config from \DOMDocument to array
 */
class TopicConfig implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert($source)
    {
        $topics = $this->extractTopics($source);
        return [
            QueueConfigInterface::TOPICS => $topics,
        ];
    }

    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractTopics($config)
    {
        $output = [];
        /** @var $topicNode \DOMElement */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicName = $topicNode->attributes->getNamedItem('name')->nodeValue;
            $topicType = $topicNode->attributes->getNamedItem('type');
            $topicHandlerName = $topicNode->attributes->getNamedItem('handlerName');
            $topicHandler = $topicNode->attributes->getNamedItem('handler');
            $topicExchange = $topicNode->attributes->getNamedItem('exchange');
            $topicConsumerInstance = $topicNode->attributes->getNamedItem('consumerInstance');
            $topicMaxMessages = $topicNode->attributes->getNamedItem('maxMessages');
            $queues = $this->extractQueuesFromTopic($topicNode);
            $output[$topicName] = [
                QueueConfigInterface::TOPIC_NAME => $topicName,
                'type' => $topicType ? $topicType->nodeValue : null,
                'exchange' => $topicExchange ? $topicExchange->nodeValue : null,
                'consumerInstance' => $topicConsumerInstance ? $topicConsumerInstance->nodeValue : null,
                'handlerName' => $topicHandlerName ? $topicHandlerName->nodeValue : null,
                'handler' => $topicHandler ? $topicHandler->nodeValue : null,
                'maxMessages' => $topicMaxMessages ? $topicMaxMessages->nodeValue : null,
                'queues' => $queues,
            ];
        }
        return $output;
    }

    /**
     * Extract queues configuration from the topic node.
     *
     * @param \DOMElement $topicNode
     * @return mixed
     */
    protected function extractQueuesFromTopic(\DOMElement $topicNode)
    {
        $queues = [];
        /** @var $queueNode \DOMElement */
        foreach ($topicNode->getElementsByTagName('queue') as $queueNode) {
            $queueName = $queueNode->attributes->getNamedItem('name')->nodeValue;
            $queueHandlerName = $queueNode->attributes->getNamedItem('handlerName');
            $queueHandler = $queueNode->attributes->getNamedItem('handler');
            $queueExchange = $queueNode->attributes->getNamedItem('exchange');
            $queueConsumer = $queueNode->attributes->getNamedItem('consumer');
            $queueConsumerInstance = $queueNode->attributes->getNamedItem('consumerInstance');
            $queueMaxMessages = $queueNode->attributes->getNamedItem('maxMessages');
            $queueType = $queueNode->attributes->getNamedItem('type');
            $queue = [];
            $queue['name'] = $queueName;
            $queue['handlerName'] = $queueHandlerName ? $queueHandlerName->nodeValue : null;
            $queue['handler'] = $queueHandler ? $queueHandler->nodeValue : null;
            $queue['exchange'] = $queueExchange ? $queueExchange->nodeValue : null;
            $queue['consumer'] = $queueConsumer ? $queueConsumer->nodeValue : null;
            $queue['consumerInstance'] = $queueConsumerInstance ? $queueConsumerInstance->nodeValue : null;
            $queue['maxMessages'] = $queueMaxMessages ? $queueMaxMessages->nodeValue : null;
            $queue['type'] = $queueType ? $queueType->nodeValue : null;
            $queues[$queueName] = $queue;
        }
        return $queues;
    }
}
