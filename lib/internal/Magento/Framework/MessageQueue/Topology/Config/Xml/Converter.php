<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Xml;

use Magento\Framework\Config\Converter\Dom\Flat as FlatConverter;
use Magento\Framework\Config\Dom\ArrayNodeConfig;
use Magento\Framework\Config\Dom\NodePathMatcher;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Converts MessageQueue topology config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface, ResetAfterRequestInterface
{
    /**
     * @var FlatConverter|null
     */
    private $converter;

    /**
     * Boolean value converter.
     *
     * @var BooleanUtils
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly BooleanUtils $booleanUtils;

    /**
     * @var InterpreterInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly InterpreterInterface $argumentInterpreter;

    /**
     * @var DefaultValueProvider
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly DefaultValueProvider $defaultValue;

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
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Magento2.Performance.ForeachArrayMerge
     */
    public function convert($source)
    {
        $result = [];
        /** @var $exchange \DOMElement */
        foreach ($source->getElementsByTagName('exchange') as $exchange) {
            $name = $this->getAttributeValue($exchange, 'name');
            $connection = $this->getAttributeValue($exchange, 'connection', $this->defaultValue->getConnection());

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

            if (isset($result[$name . '--' . $connection]['bindings']) && count($bindings) > 0) {
                $bindings = array_merge($result[$name . '--' . $connection]['bindings'], $bindings);
            }
            if (isset($result[$name . '--' . $connection]['arguments']) && count($exchangeArguments) > 0) {
                $exchangeArguments = array_merge($result[$name . '--' . $connection]['arguments'], $exchangeArguments);
            }

            $autoDelete = $this->getAttributeValue($exchange, 'autoDelete', false);
            $result[$name . '--' . $connection] = [
                'name' => $name,
                'type' => $this->getAttributeValue($exchange, 'type', 'topic'),
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
        $isDisabled = $this->booleanUtils->toBoolean(
            $this->getAttributeValue($node, 'disabled', false)
        );
        foreach ($node->childNodes as $arguments) {
            if ($arguments->nodeName != 'arguments' || $arguments->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $bindingArguments = $this->processArguments($arguments);
        }

        $destinationType = $this->getAttributeValue($node, 'destinationType', 'queue');
        $destination = $this->getAttributeValue($node, 'destination');
        $topic = $this->getAttributeValue($node, 'topic');
        $bindingId = $destinationType . '--' . $destination . '--' . $topic;

        $bindings[$bindingId] = [
            'id' => $bindingId,
            'destinationType' => $destinationType,
            'destination' => $destination,
            'disabled' => $isDisabled,
            'topic' => $topic,
            'arguments' => $bindingArguments
        ];
        return $bindings;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->converter = null;
    }
}
