<?php
/**
 * Attributes configuration converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Config;

/**
 * This converter is required for Declaration Filesystem reader:
 *
 * @see \Magento\Framework\Setup\Declaration\Schema\FileSystem\XmlReader
 *
 * Allows to convert declarative schema to raw array and add default values
 * for column types and for constraints.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config from XML to array.
     *
     * @param  \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = $this->recursiveConvert($this->getTablesNode($source));
        return $output;
    }

    /**
     * We exactly know, that our schema is consists from tables.
     * So we do not need root elements in result, only table names.
     * So proposed to select only tables from all DOMDocument.
     *
     * @param  \DOMDocument $element
     * @return \DOMNodeList
     */
    private function getTablesNode(\DOMDocument $element)
    {
        return $element->getElementsByTagName('table');
    }

    /**
     * Convert elements.
     *
     * @param \Traversable $source
     * @return array
     */
    private function recursiveConvert(\Traversable $source)
    {
        $output = [];
        foreach ($source as $element) {
            if ($element instanceof \DOMElement) {
                $key = $element->getAttribute('name');

                if ($element->hasChildNodes()) {
                    $output[$element->tagName][$key] =
                        array_replace(
                            $this->recursiveConvert($element->childNodes),
                            $this->interpretateAttributes($element)
                        );
                } else if ($this->hasAttributesExceptName($element)) {
                    $output[$element->tagName][$key] = $this->interpretateAttributes($element);
                } else {
                    $output[$element->tagName][$key] = $key;
                }
            }
        }

        return $output;
    }

    /**
     * Check whether we have any attributes except name XSI:TYPE is in another namespace.
     * Note: name is mandatory attribute.
     *
     * @param  \DOMElement $element
     * @return bool
     */
    private function hasAttributesExceptName(\DOMElement $element)
    {
        return $element->hasAttribute('xsi:type') || $element->attributes->length >= 2;
    }

    /**
     * Mix attributes that comes from XML schema with default ones.
     * So if you will not have some attribute in schema - it will be taken from default one.
     *
     * @param  \DOMElement $domElement
     * @return mixed
     */
    private function interpretateAttributes(\DOMElement $domElement)
    {
        $attributes = $this->getAttributes($domElement);
        $xsiType = $domElement->getAttribute('xsi:type');

        if ($xsiType) {
            $attributes['type'] = $xsiType;
        }

        return $attributes;
    }

    /**
     * Convert XML attributes into raw array with attributes.
     *
     * @param  \DOMElement $element
     * @return array
     */
    private function getAttributes(\DOMElement $element)
    {
        $attributes = [];
        $attributeNodes = $element->attributes;

        /** @var \DOMAttr $attribute */
        foreach ($attributeNodes as $domAttr) {
            $attributes[$domAttr->name] = $domAttr->value;
        }

        return $attributes;
    }
}
