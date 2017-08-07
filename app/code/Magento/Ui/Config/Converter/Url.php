<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Converter;

use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Ui\Config\Converter;
use Magento\Ui\Config\ConverterInterface;
use Magento\Ui\Config\ConverterUtils;

/**
 * Converter for URL type settings
 * @since 2.2.0
 */
class Url implements ConverterInterface
{
    /**
     * @var ConverterUtils
     * @since 2.2.0
     */
    private $converterUtils;

    /**
     * @param ConverterUtils $converterUtils
     * @since 2.2.0
     */
    public function __construct(ConverterUtils $converterUtils)
    {
        $this->converterUtils = $converterUtils;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function convert(\DOMNode $node, array $data = [])
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return [];
        }

        return $this->toArray($node);
    }

    /**
     * Convert nodes and child nodes to array
     *
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    public function toArray(\DOMNode $node)
    {
        $result[Converter::NAME_ATTRIBUTE_KEY] = $this->converterUtils->getComponentName($node);
        if ($node->localName != 'param') {
             $result[Dom::TYPE_ATTRIBUTE] = 'url';
        }
        if ($this->hasChildNodes($node)) {
            $result = array_merge($result, $this->processChildNodes($node));
        } else {
            $nodeValue = trim($node->nodeValue);
            if ($nodeValue !== '') {
                $result['value'] = $nodeValue;
            }
        }

        if ($node->hasAttributes() && $node->nodeType === XML_ELEMENT_NODE) {
            $result = array_merge($result, $this->processAttributes($node));
        }
        return $result;
    }

    /**
     * Check Check is DOMNode has child DOMElements
     *
     * @param \DOMNode $node
     * @return bool
     * @since 2.2.0
     */
    private function hasChildNodes(\DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType == XML_ELEMENT_NODE) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Collect node attributes
     *
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    private function processAttributes(\DOMNode $node)
    {
        $attributes = [];
        foreach ($node->attributes as $attribute) {
            if ($attribute->name == Converter::NAME_ATTRIBUTE_KEY) {
                continue;
            }
            $attributes[$attribute->nodeName] = $attribute->value;
        }

        return $attributes;
    }

    /**
     * Convert child nodes to array
     *
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    private function processChildNodes(\DOMNode $node)
    {
        $result = [];
        /** @var \DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $result['param'][$this->converterUtils->getComponentName($childNode)] = $this->toArray($childNode);
            }
        }
        return $result;
    }
}
