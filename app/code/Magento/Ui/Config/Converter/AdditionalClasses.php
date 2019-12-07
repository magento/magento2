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
 * Converter for 'settings/additionalClasses' configuration settings
 */
class AdditionalClasses implements ConverterInterface
{
    /**
     * @var ConverterUtils
     */
    private $converterUtils;

    /**
     * @param ConverterUtils $converterUtils
     */
    public function __construct(ConverterUtils $converterUtils)
    {
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
        $result = [
            'name' => $this->converterUtils->getComponentName($node),
            Dom::TYPE_ATTRIBUTE => 'array'
        ];
        if ($this->hasChildNodes($node)) {
            /** @var \DOMNode $childNode */
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                    $result['item'][$this->converterUtils->getComponentName($childNode)] = [
                        'name' => $childNode->getAttribute('name'),
                        Dom::TYPE_ATTRIBUTE => 'boolean',
                        'value' => trim($childNode->nodeValue)
                    ];
                }
            }
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
        if (!$node->hasChildNodes()) {
            return false;
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                return true;
            }
        }

        return false;
    }
}
