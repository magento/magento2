<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config\Mapper;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Stdlib\BooleanUtils;

class Dom implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ArgumentParser
     */
    private $argumentParser;

    /**
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    /**
     * @param BooleanUtils $booleanUtils
     * @param ArgumentParser $argumentParser
     * @param InterpreterInterface $argumentInterpreter
     */
    public function __construct(
        InterpreterInterface $argumentInterpreter,
        BooleanUtils $booleanUtils = null,
        ArgumentParser $argumentParser = null
    ) {
        $this->argumentInterpreter = $argumentInterpreter;
        $this->booleanUtils = $booleanUtils ?: new BooleanUtils();
        $this->argumentParser = $argumentParser ?: new ArgumentParser();
    }

    /**
     * Convert configuration in DOM format to assoc array that can be used by object manager
     *
     * @param \DOMDocument $config
     * @return array
     * @throws \Exception
     * @todo this method has high cyclomatic complexity in order to avoid performance issues
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function convert($config)
    {
        $output = [];
        /** @var \DOMNode $node */
        foreach ($config->documentElement->childNodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($node->nodeName) {
                case 'preference':
                    $output['preferences'][$node->attributes->getNamedItem(
                        'for'
                    )->nodeValue] = $node->attributes->getNamedItem(
                        'type'
                    )->nodeValue;
                    break;
                case 'type':
                case 'virtualType':
                    $typeData = [];
                    $typeNodeAttributes = $node->attributes;
                    $typeNodeShared = $typeNodeAttributes->getNamedItem('shared');
                    if ($typeNodeShared) {
                        $typeData['shared'] = $this->booleanUtils->toBoolean($typeNodeShared->nodeValue);
                    }
                    if ($node->nodeName == 'virtualType') {
                        $attributeType = $typeNodeAttributes->getNamedItem('type');
                        // attribute type is required for virtual type only in merged configuration
                        if ($attributeType) {
                            $typeData['type'] = $attributeType->nodeValue;
                        }
                    }
                    $typeArguments = [];
                    $typePlugins = [];
                    /** @var \DOMNode $typeChildNode */
                    foreach ($node->childNodes as $typeChildNode) {
                        if ($typeChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        switch ($typeChildNode->nodeName) {
                            case 'arguments':
                                /** @var \DOMNode $argumentNode */
                                foreach ($typeChildNode->childNodes as $argumentNode) {
                                    if ($argumentNode->nodeType != XML_ELEMENT_NODE) {
                                        continue;
                                    }
                                    $argumentName = $argumentNode->attributes->getNamedItem('name')->nodeValue;
                                    $argumentData = $this->argumentParser->parse($argumentNode);
                                    $typeArguments[$argumentName] = $this->argumentInterpreter->evaluate(
                                        $argumentData
                                    );
                                }
                                break;
                            case 'plugin':
                                $pluginAttributes = $typeChildNode->attributes;
                                $pluginDisabledNode = $pluginAttributes->getNamedItem('disabled');
                                $pluginSortOrderNode = $pluginAttributes->getNamedItem('sortOrder');
                                $pluginTypeNode = $pluginAttributes->getNamedItem('type');
                                $pluginData = [
                                    'sortOrder' => $pluginSortOrderNode ? (int)$pluginSortOrderNode->nodeValue : 0,
                                ];
                                if ($pluginDisabledNode) {
                                    $pluginData['disabled'] = $this->booleanUtils->toBoolean(
                                        $pluginDisabledNode->nodeValue
                                    );
                                }
                                if ($pluginTypeNode) {
                                    $pluginData['instance'] = $pluginTypeNode->nodeValue;
                                }
                                $typePlugins[$pluginAttributes->getNamedItem('name')->nodeValue] = $pluginData;
                                break;
                            default:
                                throw new \Exception(
                                    "Invalid application config. Unknown node: {$typeChildNode->nodeName}."
                                );
                        }
                    }

                    $typeData['arguments'] = $typeArguments;
                    if (!empty($typePlugins)) {
                        $typeData['plugins'] = $typePlugins;
                    }
                    $output[$typeNodeAttributes->getNamedItem('name')->nodeValue] = $typeData;
                    break;
                default:
                    throw new \Exception("Invalid application config. Unknown node: {$node->nodeName}.");
            }
        }

        return $output;
    }
}
