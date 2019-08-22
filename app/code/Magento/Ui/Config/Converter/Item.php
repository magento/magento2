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
 * Converter for array inner items
 */
class Item implements ConverterInterface
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var ConverterUtils
     */
    private $converterUtils;

    /**
     * @param ConverterInterface $converter
     * @param ConverterUtils $converterUtils
     */
    public function __construct(ConverterInterface $converter, ConverterUtils $converterUtils)
    {
        $this->converter = $converter;
        $this->converterUtils = $converterUtils;
    }

    /**
     * @inheritdoc
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
     */
    private function toArray(\DOMNode $node)
    {
        if ($node->nodeType == XML_ELEMENT_NODE && $node->getAttribute(Dom::TYPE_ATTRIBUTE) == 'url') {
            $urlResult = $this->converter->convert($node, ['type' => 'url']);
            return $urlResult ?: [];
        }

        $result[Converter::NAME_ATTRIBUTE_KEY] = $this->converterUtils->getComponentName($node);

        if ($this->hasChildNodes($node)) {
            $result = array_merge($result, $this->processChildNodes($node));
        } else {
            $result[Dom::TYPE_ATTRIBUTE] = 'string';
            if (trim($node->nodeValue) != '') {
                $result['value'] = trim($node->nodeValue);
            }
        }
        if ($node->hasAttributes() && $node->nodeType === XML_ELEMENT_NODE) {
            $result = array_merge($result, $this->processAttributes($node));
        }
        return $result;
    }

    /**
     * Check is DOMNode has child DOMElements
     *
     * @param \DOMNode $node
     * @return bool
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
     */
    private function processChildNodes(\DOMNode $node)
    {
        $result[Dom::TYPE_ATTRIBUTE] = 'array';
        /** @var \DOMNode $childNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $result['item'][$this->converterUtils->getComponentName($childNode)] = $this->toArray($childNode);
            }
        }
        return $result;
    }
}
