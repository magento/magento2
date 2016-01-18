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
            $topicName = $this->getAttributeValue($topicNode, 'name');
            $output[$topicName] = [
                QueueConfigInterface::TOPIC_NAME => $topicName,
                'type' => $this->getAttributeValue($topicNode, 'type'),
                'exchange' => $this->getAttributeValue($topicNode, 'exchange'),
                'consumerInstance' => $this->getAttributeValue($topicNode, 'consumerInstance'),
                'handlerName' => $this->getAttributeValue($topicNode, 'handlerName'),
                'handler' => $this->getAttributeValue($topicNode, 'handler'),
                'maxMessages' => $this->getAttributeValue($topicNode, 'maxMessages'),
                'queues' => $this->extractQueuesFromTopic($topicNode)
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
            $queueName = $this->getAttributeValue($queueNode, 'name');
            $queue = [
               'name'=> $queueName,
               'handlerName' => $this->getAttributeValue($queueNode, 'handlerName'),
               'handler' => $this->getAttributeValue($queueNode, 'handler'),
               'exchange' => $this->getAttributeValue($queueNode, 'exchange'),
               'consumer' => $this->getAttributeValue($queueNode, 'consumer'),
               'consumerInstance' => $this->getAttributeValue($queueNode, 'consumerInstance'),
               'maxMessages' => $this->getAttributeValue($queueNode, 'maxMessages'),
               'type' => $this->getAttributeValue($queueNode, 'type')

            ];
            $queues[$queueName] = $queue;
        }
        return $queues;
    }

    /**
     * Get attribute value of the given node
     *
     * @param \DOMNode $node
     * @param string $attributeName
     * @param mixed $default
     * @return string|null
     */
    protected function getAttributeValue(\DOMNode $node, $attributeName, $default = null)
    {
        $item =  $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }
}
