<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Xml\Reader;

use Magento\Framework\MessageQueue\Config\Validator;
use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\Communication\Config\ConfigParser;

/**
 * Converts MessageQueue consumers config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const DEFAULT_CONNECTION = 'amqp';
    const DEFAULT_INSTANCE = ConsumerInterface::class;

    /**
     * @var ConfigParser
     */
    private $configParser;

    /**
     * Initialize dependencies.
     *
     * @param ConfigParser $configParser
     */
    public function __construct(
        ConfigParser $configParser
    ) {
        $this->configParser = $configParser;
    }

    /**
     * {@inheritDoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var $consumerNode \DOMElement */
        foreach ($source->getElementsByTagName('consumer') as $consumerNode) {
            $consumerName = $this->getAttributeValue($consumerNode, 'name');
            $handler = $this->getAttributeValue($consumerNode, 'handler');
            $result[$consumerName] = [
                'name' => $consumerName,
                'queue' => $this->getAttributeValue($consumerNode, 'queue'),
                'consumerInstance' => $this->getAttributeValue(
                    $consumerNode,
                    'consumerInstance',
                    self::DEFAULT_INSTANCE
                ),
                'handlers' => $handler ? [$this->configParser->parseServiceMethod($handler)] : [],
                'connection' => $this->getAttributeValue(
                    $consumerNode,
                    'connection',
                    self::DEFAULT_CONNECTION
                ),
                'maxMessages' => $this->getAttributeValue($consumerNode, 'maxMessages')
            ];
        }
        return $result;
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
        $item = $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }
}
