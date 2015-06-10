<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\ExtensionAttribute\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const RESOURCE_PERMISSIONS = "resourceRefs";
    const DATA_TYPE = "type";
    const JOIN_DIRECTIVE = "join";
    const JOIN_REFERENCE_TABLE = "join_reference_table";
    const JOIN_REFERENCE_FIELD = "join_reference_field";
    const JOIN_SELECT_FIELDS = "join_select_fields";
    const JOIN_SELECT_FIELD = "select_field";
    const JOIN_SELECT_FIELD_SETTER = "setter_name";
    const JOIN_JOIN_ON_FIELD= "join_join_on_field";

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

                $joinElement = $attribute->getElementsByTagName('join')->item(0);
                $join = $this->processJoinElement($joinElement, $attribute);

                $typeConfig[$code] = [
                    self::DATA_TYPE => $codeType,
                    self::RESOURCE_PERMISSIONS => $resourceRefs,
                    self::JOIN_DIRECTIVE => $join,
                ];
            }

            $output[$typeName] = $typeConfig;
        }
        return $output;
    }

    /**
     * Process the join element configuration
     *
     * @param \DOMElement $joinElement
     * @param \DOMElement $attribute
     * @return array
     */
    private function processJoinElement($joinElement, $attribute)
    {
        $join = null;
        if ($joinElement && $joinElement->nodeType === XML_ELEMENT_NODE) {
            $joinAttributes = $joinElement->attributes;
            $join = [
                self::JOIN_REFERENCE_TABLE => $joinAttributes->getNamedItem('reference_table')->nodeValue,
                self::JOIN_JOIN_ON_FIELD => $joinAttributes->getNamedItem('join_on_field')->nodeValue,
                self::JOIN_REFERENCE_FIELD => $joinAttributes->getNamedItem('reference_field')->nodeValue,
            ];
            $selectElements = $attribute->getElementsByTagName('select_field');
            foreach ($selectElements as $selectElement) {
                $selectField = $selectElement->nodeValue;
                $setterName = $selectElement->getAttribute('setter_name');
                $join[self::JOIN_SELECT_FIELDS][] = [
                    self::JOIN_SELECT_FIELD => $selectField,
                    self::JOIN_SELECT_FIELD_SETTER => $setterName
                ];
            }
        }

        return $join;
    }
}
