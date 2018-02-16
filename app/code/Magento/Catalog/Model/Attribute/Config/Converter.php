<?php
/**
 * Converter of attributes configuration from \DOMDocument to array
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Config;

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
        $result = [];
        /** @var DOMNode $groupNode */
        foreach ($source->documentElement->childNodes as $groupNode) {
            if ($groupNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $groupName = $groupNode->attributes->getNamedItem('name')->nodeValue;
            /** @var DOMNode $groupAttributeNode */
            foreach ($groupNode->childNodes as $groupAttributeNode) {
                if ($groupAttributeNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $groupAttributeName = $groupAttributeNode->attributes->getNamedItem('name')->nodeValue;
                $result[$groupName][] = $groupAttributeName;
            }
        }
        return $result;
    }
}
