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
            $queueName = $this->getAttributeValue($queueNode, 'name');
            $topics = [];
            /** @var $topicNode \DOMNode */
            foreach ($queueNode->childNodes as $topicNode) {
                if ($topicNode->hasAttributes()) {
                    $topicName = $this->getAttributeValue($topicNode, 'name');
                    $topics[$topicName] = [
                        'name'        => $topicName,
                        'handlerName' => $this->getAttributeValue($topicNode, 'handlerName'),
                        'handler'     => $this->getAttributeValue($topicNode, 'handler')
                    ];
                }
            }
            $output[$queueName] = [
                'name' => $queueName,
                'exchange' => $this->getAttributeValue($queueNode, 'exchange'),
                'consumer' => $this->getAttributeValue($queueNode, 'consumer'),
                'consumerInstance' => $this->getAttributeValue($queueNode, 'consumerInstance'),
                'type' => $this->getAttributeValue($queueNode, 'type'),
                'topics' => $topics
            ];
        }
        return $output;
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
