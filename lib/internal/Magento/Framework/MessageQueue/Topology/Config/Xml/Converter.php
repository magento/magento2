<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Xml;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Config\Converter\Dom\Flat as FlatConverter;
use Magento\Framework\Config\Dom\ArrayNodeConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;
use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Converts MessageQueue topology config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var FlatConverter
     */
    private $converter;

    /**
     * Boolean value converter.
     *
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Argument interpreter.
     *
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    /**
     * @var DefaultValueProvider
     */
    private $defaultValue;

    /**
     * Initialize dependencies.
     *
     * @param BooleanUtils $booleanUtils
     * @param InterpreterInterface $argumentInterpreter
     * @param DefaultValueProvider $defaultValueProvider
     */
    public function __construct(
        BooleanUtils $booleanUtils,
        InterpreterInterface $argumentInterpreter,
        DefaultValueProvider $defaultValueProvider
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->defaultValue = $defaultValueProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        /** @var $exchange \DOMElement */
        foreach ($source->getElementsByTagName('exchange') as $exchange) {
            $name = $this->getAttributeValue($exchange, 'name');
            $connection = $this->getAttributeValue($exchange, 'connection');

            $bindings = [];
            $exchangeArguments = [];
            /** @var \DOMNode $node */
            foreach ($exchange->childNodes as $node) {
                if (!in_array($node->nodeName, ['binding', 'arguments']) || $node->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                switch ($node->nodeName) {
                    case 'binding':
                        $bindings = $this->processBindings($node, $bindings);
                        break;

                    case 'arguments':
                        $exchangeArguments = $this->processArguments($node);
                        break;
                }
            }

            $autoDelete = $this->getAttributeValue($exchange, 'autoDelete', false);
            $result[$name . '--' . $connection] = [
                'name' => $name,
                'type' => $this->getAttributeValue($exchange, 'type'),
                'connection' => $connection,
                'durable' => $this->booleanUtils->toBoolean($this->getAttributeValue($exchange, 'durable', true)),
                'autoDelete' => $this->booleanUtils->toBoolean($autoDelete),
                'internal' => $this->booleanUtils->toBoolean($this->getAttributeValue($exchange, 'internal', false)),
                'bindings' => $bindings,
                'arguments' => $exchangeArguments,
            ];
        }
        return $result;
    }

    /**
     * Retrieve instance of XML converter
     *
     * @return FlatConverter
     */
    private function getConverter()
    {
        if (!$this->converter) {
            $arrayNodeConfig = new ArrayNodeConfig(new NodePathMatcher(), ['argument(/item)+' => 'name']);
            $this->converter = new FlatConverter($arrayNodeConfig);
        }
        return $this->converter;
    }

    /**
     * Process arguments.
     *
     * @param \DOMNode $node
     * @return array
     */
    private function processArguments(\DOMNode $node)
    {
        $output = [];
        /** @var \DOMNode $argumentNode */
        foreach ($node->childNodes as $argumentNode) {
            if ($argumentNode->nodeType != XML_ELEMENT_NODE || $argumentNode->nodeName != 'argument') {
                continue;
            }
            $argumentName = $argumentNode->attributes->getNamedItem('name')->nodeValue;
            $argumentData = $this->getConverter()->convert($argumentNode, 'argument');
            $output[$argumentName] = $this->argumentInterpreter->evaluate($argumentData);
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
    private function getAttributeValue(\DOMNode $node, $attributeName, $default = null)
    {
        $item = $node->attributes->getNamedItem($attributeName);
        return $item ? $item->nodeValue : $default;
    }

    /**
     * Parse bindings.
     *
     * @param \DOMNode $node
     * @param array $bindings
     * @return array
     */
    private function processBindings($node, $bindings)
    {
        $bindingArguments = [];
        $id = $this->getAttributeValue($node, 'id');
        $isDisabled = $this->booleanUtils->toBoolean(
            $this->getAttributeValue($node, 'disabled', false)
        );
        foreach ($node->childNodes as $arguments) {
            if ($arguments->nodeName != 'arguments' || $arguments->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $bindingArguments = $this->processArguments($arguments);
        }
        $bindings[$id] = [
            'id' => $id,
            'destinationType' => $this->getAttributeValue($node, 'destinationType'),
            'destination' => $this->getAttributeValue($node, 'destination'),
            'disabled' => $isDisabled,
            'topic' => $this->getAttributeValue($node, 'topic'),
            'arguments' => $bindingArguments
        ];
        return $bindings;
    }
}
