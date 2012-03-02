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
 * @package     Mage_Widget
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Instance block reference chooser
 *
 * @category    Mage
 * @package     Mage_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Block
    extends Mage_Adminhtml_Block_Widget
{
    protected $_layoutHandlesXml = null;

    protected $_layoutHandleUpdates = array();

    protected $_layoutHandleUpdatesXml = null;

    protected $_layoutHandle = array();

    protected $_blocks = array();

    protected $_allowedBlocks = array();

    /**
     * Setter
     *
     * @param array $allowedBlocks
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Block
     */
    public function setAllowedBlocks($allowedBlocks)
    {
        $this->_allowedBlocks = $allowedBlocks;
        return $this;
    }

    /**
     * Add allowed block
     *
     * @param string $block
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Block
     */
    public function addAllowedBlock($block)
    {
        $this->_allowedBlocks[] = $block;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getAllowedBlocks()
    {
        return $this->_allowedBlocks;
    }

    /**
     * Setter
     * If string given exlopde to array by ',' delimiter
     *
     * @param string|array $layoutHandle
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Block
     */
    public function setLayoutHandle($layoutHandle)
    {
        if (is_string($layoutHandle)) {
            $layoutHandle = explode(',', $layoutHandle);
        }
        $this->_layoutHandle = array_merge(array('default'), (array)$layoutHandle);
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getLayoutHandle()
    {
        return $this->_layoutHandle;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getArea()
    {
        if (!$this->_getData('area')) {
            return Mage_Core_Model_Design_Package::DEFAULT_AREA;
        }
        return $this->_getData('area');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getPackage()
    {
        if (!$this->_getData('package')) {
            return Mage_Core_Model_Design_Package::DEFAULT_PACKAGE;
        }
        return $this->_getData('package');
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTheme()
    {
        if (!$this->_getData('theme')) {
            return Mage_Core_Model_Design_Package::DEFAULT_THEME;
        }
        return $this->_getData('theme');
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $selectBlock = $this->getLayout()->createBlock('Mage_Core_Block_Html_Select')
            ->setName('block')
            ->setClass('required-entry select')
            ->setExtraParams('onchange="WidgetInstance.loadSelectBoxByType(\'block_template\','
                .' this.up(\'div.group_container\'), this.value)"')
            ->setOptions($this->getBlocks())
            ->setValue($this->getSelected());
        return parent::_toHtml().$selectBlock->toHtml();
    }

    /**
     * Retrieve blocks array
     *
     * @return array
     */
    public function getBlocks()
    {
        if (empty($this->_blocks)) {
            /* @var $update Mage_Core_Model_Layout_Update */
            $update = Mage::getModel('Mage_Core_Model_Layout')->getUpdate();
            /* @var $layoutHandles Mage_Core_Model_Layout_Element */
            $this->_layoutHandlesXml = $update->getFileLayoutUpdatesXml(
                $this->getArea(),
                $this->getPackage(),
                $this->getTheme());
            $this->_collectLayoutHandles();
            $this->_collectBlocks();
            array_unshift($this->_blocks, array(
                'value' => '',
                'label' => Mage::helper('Mage_Widget_Helper_Data')->__('-- Please Select --')
            ));
        }
        return $this->_blocks;
    }

    /**
     * Merging layout handles and create xml of merged layout handles
     *
     */
    protected function _collectLayoutHandles()
    {
        foreach ($this->getLayoutHandle() as $handle) {
            $this->_mergeLayoutHandles($handle);
        }
        $updatesStr = '<'.'?xml version="1.0"?'.'><layout>'.implode('', $this->_layoutHandleUpdates).'</layout>';
        $this->_layoutHandleUpdatesXml = simplexml_load_string($updatesStr, 'Varien_Simplexml_Element');
    }

    /**
     * Adding layout handle that specified in node 'update' to general layout handles
     *
     * @param string $handle
     */
    public function _mergeLayoutHandles($handle)
    {
        foreach ($this->_layoutHandlesXml->{$handle} as $updateXml) {
            foreach ($updateXml->children() as $child) {
                if (strtolower($child->getName()) == 'update' && isset($child['handle'])) {
                    $this->_mergeLayoutHandles((string)$child['handle']);
                }
            }
            $this->_layoutHandleUpdates[] = $updateXml->asNiceXml();
        }
    }


    /**
     * Filter and collect blocks into array
     */
    protected function _collectBlocks()
    {
        if ($blocks = $this->_layoutHandleUpdatesXml->xpath('//block/label/..')) {
            /* @var $block Mage_Core_Model_Layout_Element */
            foreach ($blocks as $block) {
                if ((string)$block->getAttribute('name') && $this->_filterBlock($block)) {
                    $helper = Mage::helper(Mage_Core_Model_Layout::findTranslationModuleName($block));
                    $this->_blocks[(string)$block->getAttribute('name')] = $helper->__((string)$block->label);
                }
            }
        }
        asort($this->_blocks, SORT_STRING);
    }

    /**
     * Check whether given block match allowed block types
     *
     * @param Mage_Core_Model_Layout_Element $block
     * @return boolean
     */
    protected function _filterBlock($block)
    {
        if (!$this->getAllowedBlocks()) {
            return true;
        }
        if (in_array((string)$block->getAttribute('name'), $this->getAllowedBlocks())) {
            return true;
        }
        return false;
    }
}
