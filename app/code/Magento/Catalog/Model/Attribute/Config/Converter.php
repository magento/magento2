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
            /** @noinspection PhpUndefinedFieldInspection */
            if ($groupNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            /** @noinspection PhpUndefinedFieldInspection */
            $groupName = $groupNode->attributes->getNamedItem('name')->nodeValue;
            /** @var DOMNode $groupAttributeNode */
            /** @noinspection PhpUndefinedFieldInspection */
            foreach ($groupNode->childNodes as $groupAttributeNode) {
                /** @noinspection PhpUndefinedFieldInspection */
                if ($groupAttributeNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $groupAttributeName = $groupAttributeNode->attributes->getNamedItem('name')->nodeValue;
                $result[$groupName][] = $groupAttributeName;
            }
        }
        return $result;
    }
}
