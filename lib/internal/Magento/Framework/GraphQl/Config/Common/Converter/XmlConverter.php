<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Common\Converter;

use Magento\Framework\Config\ConverterInterface;

/**
 * GraphQL configuration converter.
 *
 * Converts configuration data stored in XML format into corresponding PHP array.
 */
class XmlConverter implements ConverterInterface
{
    /**
     * Converts GraphQL XML node describing schema into processable array.
     *
     * @param \DOMNode $source
     * @return array|string
     */
    private function convertNodeToArray(\DOMNode $source)
    {
        $converted = [];
        if ($source->hasAttributes()) {
            $attributes = $source->attributes;
            foreach ($attributes as $attribute) {
                $converted[$attribute->name] = $attribute->value;
            }
        }
        if ($source->hasChildNodes()) {
            $childNodes = $source->childNodes;
            if ($childNodes->length == 1) {
                $child = $childNodes->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $converted['_value'] = $child->nodeValue;
                    return count($converted) == 1 ? $converted['_value'] : $converted;
                }
            }
            foreach ($childNodes as $child) {
                if (! $child instanceof \DOMCharacterData) {
                    $converted[$child->nodeName][] = $this->convertNodeToArray($child);
                }
            }
        }
        return $converted;
    }

    /**
     * Converts GraphQL XML document describing schema into processable array.
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        return $this->convertNodeToArray($source);
    }
}
