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
class Mage_Xml_Generator
{
    protected $_dom = null;
    protected $_currentDom;

    public function __construct()
    {
        $this->_dom = new DOMDocument('1.0');
        $this->_dom->formatOutput=true;
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

    /**
    * @param array $content
    */
    public function arrayToXml($content)
    {
        $parentNode = $this->_getCurrentDom();
        if(!$content || !count($content)) {
            return $this;
        }        
        foreach ($content as $_key=>$_item) {
            try{
                $node = $this->getDom()->createElement($_key);
            } catch (DOMException $e) {
              //  echo $e->getMessage();
                var_dump($_item);
                die;
            }
            $parentNode->appendChild($node);
            if (is_array($_item) && isset($_item['_attribute'])) {
                if (is_array($_item['_value'])) {
                    if (isset($_item['_value'][0])) {
                        foreach($_item['_value'] as $_k=>$_v) {
                            $this->_setCurrentDom($node)->arrayToXml($_v);
                        }
                    } else {
                        $this->_setCurrentDom($node)->arrayToXml($_item['_value']);
                    }
                } else {
                    $child = $this->getDom()->createTextNode($_item['_value']);
                    $node->appendChild($child);
                }
                foreach($_item['_attribute'] as $_attributeKey=>$_attributeValue) {
                    $node->setAttribute($_attributeKey, $_attributeValue);
                }
            } elseif (is_string($_item)) {
                $text = $this->getDom()->createTextNode($_item);
                $node->appendChild($text);
            } elseif (is_array($_item) && !isset($_item[0])) {
                $this->_setCurrentDom($node)->arrayToXml($_item);
            } elseif (is_array($_item) && isset($_item[0])) {
                foreach($_item as $k=>$v) {
                    $this->_setCurrentDom($node)->arrayToXml($v);
                }
            }
        }
        return $this;
    }

    public function __toString()
    {
        return $this->getDom()->saveXML();
    }

    public function save($file)
    {
        $this->getDom()->save($file);
        return $this;
    }

}
