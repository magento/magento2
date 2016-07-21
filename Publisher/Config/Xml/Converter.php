<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Xml;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Converts MessageQueue publishers config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Default exchange name
     *
     * @var string
     */
    private static $defaultExchange = 'magento';

    /**
     * Boolean value converter.
     *
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Initialize dependencies.
     *
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var $publisherConfig \DOMElement */
        foreach ($source->getElementsByTagName('publisher') as $publisherConfig) {
            $topic = $this->getAttributeValue($publisherConfig, 'topic');

            $connections = [];
            /** @var \DOMNode $connectionConfig */
            foreach ($publisherConfig->childNodes as $connectionConfig) {
                if ($connectionConfig->nodeName != 'connection' || $connectionConfig->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $connectionName = $this->getAttributeValue($connectionConfig, 'name');
                if (!$connectionName) {
                    throw new \InvalidArgumentException('Connection name is missing');
                }
                $exchangeName = $this->getAttributeValue($connectionConfig, 'exchange', self::$defaultExchange);
                $isDisabled = $this->getAttributeValue($connectionConfig, 'disabled', false);
                $connections[$connectionName] = [
                    'name' => $connectionName,
                    'exchange' => $exchangeName,
                    'disabled' => $this->booleanUtils->toBoolean($isDisabled),
                ];
            }
            $isDisabled = $this->getAttributeValue($publisherConfig, 'disabled', false);
            $result[$topic] = [
                'topic' => $topic,
                'disabled' => $this->booleanUtils->toBoolean($isDisabled),
                'connections' => $connections,

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
