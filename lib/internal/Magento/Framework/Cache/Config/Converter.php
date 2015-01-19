<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $types */
        $types = $source->getElementsByTagName('type');
        /** @var \DOMNode $type */
        foreach ($types as $type) {
            $typeConfig = [];
            foreach ($type->attributes as $attribute) {
                $typeConfig[$attribute->nodeName] = $attribute->nodeValue;
            }
            /** @var \DOMNode $childNode */
            foreach ($type->childNodes as $childNode) {
                if ($childNode->nodeType == XML_ELEMENT_NODE ||
                    ($childNode->nodeType == XML_CDATA_SECTION_NODE ||
                    $childNode->nodeType == XML_TEXT_NODE && trim(
                        $childNode->nodeValue
                    ) != '')
                ) {
                    $typeConfig[$childNode->nodeName] = $childNode->nodeValue;
                }
            }
            $output[$type->attributes->getNamedItem('name')->nodeValue] = $typeConfig;
        }
        return ['types' => $output];
    }
}
