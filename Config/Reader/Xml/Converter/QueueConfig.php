<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader\Xml\Converter;

/**
 * Class QueueConfig to handle <queue> root node MQ config type
 *
 */
class QueueConfig implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert($source)
    {
        $queues = $this->extractQueues($source);
        return $queues;
    }

    /**
     * Extract configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractQueues(\DOMDocument $config)
    {
        $output = [];
        /** @var $queueNode \DOMNode */
        foreach ($config->getElementsByTagName('queue') as $queueNode) {
            $queueName = $queueNode->attributes->getNamedItem('name')->nodeValue;
            $exchange = $queueNode->attributes->getNamedItem('exchange')->nodeValue;
            $consumer = $queueNode->attributes->getNamedItem('consumer')->nodeValue;
            $consumerInstance = $queueNode->attributes->getNamedItem('consumerInstance')->nodeValue;
            $topics = [];
            /** @var $topicNode \DOMNode */
            foreach ($queueNode->childNodes as $topicNode) {
                if ($topicNode->hasAttributes()) {
                    $topicName = $topicNode->hasAttribute('name') ?
                        $topicNode->attributes->getNamedItem('name')->nodeValue
                        : "";
                    $handlerName = $topicNode->hasAttribute('handlerName') ?
                        $topicNode->attributes->getNamedItem('handlerName')->nodeValue
                        : "";
                    $handler = $topicNode->hasAttribute('handler') ?
                        $topicNode->attributes->getNamedItem('handler')->nodeValue
                        : "";
                    $topics[$topicName] = [
                        'name'        => $topicName,
                        'handlerName' => $handlerName,
                        'handler'     => $handler
                    ];
                }
            }
            $output[$queueName] = [
                'name' => $queueName,
                'exchange' => $exchange,
                'consumer' => $consumer,
                'consumerInstance' => $consumerInstance,
                'topics' => $topics
            ];
        }
        return $output;
    }
}
