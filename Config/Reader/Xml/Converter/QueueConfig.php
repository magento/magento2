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
        return $this->extractQueues($source);
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
            // required attributes
            $queueName = $queueNode->attributes->getNamedItem('name')->nodeValue;
            $exchange = $queueNode->attributes->getNamedItem('exchange')->nodeValue;
            $queueType = $queueNode->attributes->getNamedItem('type')->nodeValue;
            // optional attributes
            $consumer = $queueNode->hasAttribute('consumer') ?
                $queueNode->attributes->getNamedItem('consumer')->nodeValue
                : null;
            $consumerInstance = $queueNode->hasAttribute('consumerInstance') ?
                $queueNode->attributes->getNamedItem('consumerInstance')->nodeValue
                : null;
            $topics = [];
            /** @var $topicNode \DOMNode */
            foreach ($queueNode->childNodes as $topicNode) {
                if ($topicNode->hasAttributes()) {
                    // required attribute
                    $topicName = $topicNode->attributes->getNamedItem('name')->nodeValue;
                    // optional attributes
                    $handlerName = $topicNode->hasAttribute('handlerName') ?
                        $topicNode->attributes->getNamedItem('handlerName')->nodeValue
                        : null;
                    $handler = $topicNode->hasAttribute('handler') ?
                        $topicNode->attributes->getNamedItem('handler')->nodeValue
                        : null;

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
                'type' => $queueType,
                'topics' => $topics
            ];
        }
        return $output;
    }
}
