<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Converter;

/**
 * Class \Magento\Framework\Config\Converter\Dom
 *
 * @since 2.0.0
 */
class Dom implements \Magento\Framework\Config\ConverterInterface
{
    const ATTRIBUTES = '__attributes__';

    const CONTENT = '__content__';

    /**
     * Convert dom node tree to array
     *
     * @param mixed $source
     * @return array
     * @since 2.0.0
     */
    public function convert($source)
    {
        $nodeListData = [];

        /** @var $node \DOMNode */
        foreach ($source->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $nodeData = [];
                /** @var $attribute \DOMNode */
                foreach ($node->attributes as $attribute) {
                    if ($attribute->nodeType == XML_ATTRIBUTE_NODE) {
                        $nodeData[self::ATTRIBUTES][$attribute->nodeName] = $attribute->nodeValue;
                    }
                }
                $childrenData = $this->convert($node);

                if (is_array($childrenData)) {
                    $nodeData = array_merge($nodeData, $childrenData);
                } else {
                    $nodeData[self::CONTENT] = $childrenData;
                }
                $nodeListData[$node->nodeName][] = $nodeData;
            } elseif ($node->nodeType == XML_CDATA_SECTION_NODE || $node->nodeType == XML_TEXT_NODE && trim(
                $node->nodeValue
            ) != ''
            ) {
                return $node->nodeValue;
            }
        }
        return $nodeListData;
    }
}
