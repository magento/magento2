<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $fieldsets = array();
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
        $result = array();
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
        $result = array();
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
        $result = array();
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
