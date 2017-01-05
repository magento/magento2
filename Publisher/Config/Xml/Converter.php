<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Xml;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Converts MessageQueue publishers config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Boolean value converter.
     *
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Default value provider.
     *
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param BooleanUtils $booleanUtils
     * @param DefaultValueProvider $defaultValueProvider
     */
    public function __construct(BooleanUtils $booleanUtils, DefaultValueProvider $defaultValueProvider)
    {
        $this->booleanUtils = $booleanUtils;
        $this->defaultValueProvider = $defaultValueProvider;
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
                $exchangeName = $this->getAttributeValue(
                    $connectionConfig,
                    'exchange',
                    $this->defaultValueProvider->getExchange()
                );
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
    private function getAttributeValue(\DOMNode $node, $attributeName, $default = null)
    {
        $item = $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }
}
