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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Visual design editor element collection
 */
class Mage_DesignEditor_Model_History_Manager_Collection
{
    /**
     * Elements collection
     *
     * @var array
     */
    protected $_elements = array();

    /**
     * Xml types
     *
     * @var array
     */
    protected $_xmlTypes = array('layout');

    /**
     * Get types
     *
     * @return array
     */
    protected function _getElements()
    {
        return $this->_elements;
    }

    /**
     * Get element
     *
     * @param string $type
     * @param string $name
     * @return Mage_DesignEditor_Model_History_Manager_Adapter_Abstract
     */
    protected function _getElement($type, $name)
    {
        if (!isset($this->_elements[$type][$name])) {
            $this->_elements[$type][$name] = $this->_createElementByType($type);
        }
        return $this->_elements[$type][$name];
    }

    /**
     * Create element by type
     *
     * @param string $type
     * @return Mage_DesignEditor_Model_History_Manager_Adapter_Abstract
     */
    protected function _createElementByType($type)
    {
        return Mage_DesignEditor_Model_History_Manager_Adapter::factory($type);
    }

    /**
     * Get elements by type
     *
     * @param string $type
     * @return array|Mage_DesignEditor_Model_History_Manager_Adapter_Abstract
     */
    protected function _getElementsByType($type)
    {
        return isset($this->_elements[$type]) ? $this->_elements[$type] : array();
    }

    /**
     * Add element
     *
     * @param string $change
     * @return Mage_DesignEditor_Model_History_Manager_Adapter_Abstract
     */
    public function addElement($change)
    {
        $handle = $change['handle'];
        $type   = $change['change_type'];
        $name   = $change['element_name'];
        $action = $change['action_name'];
        $data   = isset($change['action_data']) ? $change['action_data'] : '';

        $element = $this->_getElement($type, $name);
        $element->setHandle($handle)->setType($type)->setName($name)->addAction($action, $data);
        return $this;
    }

    /**
     * Collection to xml
     *
     * @return string
     */
    public function toXml()
    {
        /** @var $xmlObject Varien_Simplexml_Element */
        $xmlObject = new Varien_Simplexml_Element('<layout></layout>');

        foreach ($this->_xmlTypes as $type) {
            foreach ($this->_getElementsByType($type) as $element) {
                $handleObject = $this->_getChildByHandle($xmlObject, $element->getHandle());
                $element->setHandleObject($handleObject)->render();
            }
        }

        return $xmlObject->asNiceXml();
    }

    /**
     * Get child by handle
     *
     * @param Varien_Simplexml_Element $xmlObject
     * @param string $handle
     * @return Varien_Simplexml_Element
     */
    protected function _getChildByHandle($xmlObject, $handle)
    {
        foreach ($xmlObject->children() as $child) {
            if ($child->getName() == $handle) {
                return $child;
            }
        }
        return $xmlObject->addChild($handle);
    }

    /**
     * Collection to history log
     *
     * @return array
     */
    public function toHistoryLog()
    {
        $historyLog = array();

        foreach ($this->_getElements() as $elementsByType) {
            foreach ($elementsByType as $element) {
                $historyLog = array_merge($historyLog, $element->toHistoryLog());
            }
        }

        return $historyLog;
    }
}
