<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Object\Copy\Config;

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
        $fieldsets = [];
        $xpath = new \DOMXPath($source);
        /** @var \DOMNode $fieldset */
        foreach ($xpath->query('/config/scope') as $scope) {
            $scopeId = $scope->attributes->getNamedItem('id')->nodeValue;
            $fieldsets[$scopeId] = $this->_convertScope($scope);
        }
        return $fieldsets;
    }

    /**
     * Convert Scope node to Magento array
     *
     * @param \DOMNode $scope
     * @return array
     */
    protected function _convertScope($scope)
    {
        $result = [];
        foreach ($scope->childNodes as $fieldset) {
            if (!$fieldset instanceof \DOMElement) {
                continue;
            }
            $fieldsetName = $fieldset->attributes->getNamedItem('id')->nodeValue;
            $result[$fieldsetName] = $this->_convertFieldset($fieldset);
        }
        return $result;
    }

    /**
     * Convert Fieldset node to Magento array
     *
     * @param \DOMNode $fieldset
     * @return array
     */
    protected function _convertFieldset($fieldset)
    {
        $result = [];
        foreach ($fieldset->childNodes as $field) {
            if (!$field instanceof \DOMElement) {
                continue;
            }
            $fieldName = $field->attributes->getNamedItem('name')->nodeValue;
            $result[$fieldName] = $this->_convertField($field);
        }
        return $result;
    }

    /**
     * Convert Field node to Magento array
     *
     * @param \DOMNode $field
     * @return array
     */
    protected function _convertField($field)
    {
        $result = [];
        foreach ($field->childNodes as $aspect) {
            if (!$aspect instanceof \DOMElement) {
                continue;
            }
            /** @var \DOMNamedNodeMap $aspectAttributes */
            $aspectAttributes = $aspect->attributes;
            $aspectName = $aspectAttributes->getNamedItem('name')->nodeValue;
            $targetField = $aspectAttributes->getNamedItem('targetField');
            $result[$aspectName] = is_null($targetField) ? '*' : $targetField->nodeValue;
        }
        return $result;
    }
}
