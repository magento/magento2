<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const RESOURCE_PERMISSIONS = "resourceRefs";
    const DATA_TYPE = "type";

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        /** @var \DOMNodeList $types */
        $types = $source->getElementsByTagName('extension_attributes');
        /** @var \DOMNode $type */
        foreach ($types as $type) {
            $typeConfig = [];
            $typeName = $type->getAttribute('for');

            $attributes = $type->getElementsByTagName('attribute');
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttribute('code');
                $codeType = $attribute->getAttribute('type');

                $resourcesElement = $attribute->getElementsByTagName('resources')->item(0);
                $resourceRefs = [];
                if ($resourcesElement && $resourcesElement->nodeType === XML_ELEMENT_NODE) {
                    $singleResourceElements = $resourcesElement->getElementsByTagName('resource');
                    foreach ($singleResourceElements as $element) {
                        if ($element->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        $resourceRefs[] = $element->attributes->getNamedItem('ref')->nodeValue;
                    }
                }

                $typeConfig[$code] = [
                    self::DATA_TYPE => $codeType,
                    self::RESOURCE_PERMISSIONS => $resourceRefs,
                ];
            }

            $output[$typeName] = $typeConfig;
        }
        return $output;
    }
}
