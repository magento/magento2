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
 * @category    Mage
 * @package     Mage_Xml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Xml_Parser
{
    protected $_dom = null;
    protected $_currentDom;
    protected $_content = array();

    public function __construct()
    {
        $this->_dom = new DOMDocument;
        $this->_currentDom = $this->_dom;
        return $this;
    }

    public function getDom()
    {
        return $this->_dom;
    }

    protected function _getCurrentDom()
    {
        return $this->_currentDom;
    }

    protected function _setCurrentDom($node)
    {
        $this->_currentDom = $node;
        return $this;
    }

    public function xmlToArray()
    {
        $this->_content = $this->_xmlToArray();
        return $this->_content;
    }

    protected function _xmlToArray($currentNode=false)
    {
        if (!$currentNode) {
            $currentNode = $this->getDom();
        }
        $content = array();
        foreach ($currentNode->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:

                    $value = null;
                    if ($node->hasChildNodes()) {
                        $value = $this->_xmlToArray($node);
                    }
                    $attributes = array();
                    if ($node->hasAttributes()) {
                        foreach($node->attributes as $attribute) {
                            $attributes += array($attribute->name=>$attribute->value);
                        }
                        $value = array('_value'=>$value, '_attribute'=>$attributes);
                    }
                    if (isset($content[$node->nodeName])) {
                        if (!isset($content[$node->nodeName][0]) || !is_array($content[$node->nodeName][0])) {
                            $oldValue = $content[$node->nodeName];
                            $content[$node->nodeName] = array();
                            $content[$node->nodeName][] = $oldValue;
                        }
                        $content[$node->nodeName][] = $value;
                    } else {
                        $content[$node->nodeName] = $value;
                    }
                    break;
                case XML_TEXT_NODE:
                    if (trim($node->nodeValue)) {
                        $content = $node->nodeValue;
                    }
                    break;
            }
        }
        return $content;
    }

    public function load($file)
    {
        $this->getDom()->load($file);
        return $this;
    }

    public function loadXML($string)
    {
        $this->getDom()->loadXML($string);
        return $this;
    }

}
