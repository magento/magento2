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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Instance layouts chooser
 *
 * @category    Mage
 * @package     Mage_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Layout
    extends Mage_Adminhtml_Block_Widget
{
    protected $_layoutHandles = array();

    /**
     * layout handles wildcar patterns
     *
     * @var array
     */
    protected $_layoutHandlePatterns = array(
        '^default$',
        '^catalog_category_*',
        '^catalog_product_*',
        '^PRODUCT_*'
    );

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Add not allowed layout handle pattern
     *
     * @param string $pattern
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Layout
     */
    public function addLayoutHandlePattern($pattern)
    {
        $this->_layoutHandlePatterns[] = $pattern;
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getLayoutHandlePatterns()
    {
        return $this->_layoutHandlePatterns;
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
            ->setName($this->getSelectName())
            ->setId('layout_handle')
            ->setClass('required-entry select')
            ->setExtraParams("onchange=\"WidgetInstance.loadSelectBoxByType(\'block_reference\', " .
                            "this.up(\'div.pages\'), this.value)\"")
            ->setOptions($this->getLayoutHandles(
                $this->getArea(),
                $this->getPackage(),
                $this->getTheme()));
        return parent::_toHtml().$selectBlock->toHtml();
    }

    /**
     * Retrieve layout handles
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return array
     */
    public function getLayoutHandles($area, $package, $theme)
    {
        if (empty($this->_layoutHandles)) {
            /* @var $update Mage_Core_Model_Layout_Update */
            $update = Mage::getModel('Mage_Core_Model_Layout')->getUpdate();
            $this->_layoutHandles[''] = Mage::helper('Mage_Widget_Helper_Data')->__('-- Please Select --');
            $this->_collectLayoutHandles($update->getFileLayoutUpdatesXml($area, $package, $theme));
        }
        return $this->_layoutHandles;
    }

    /**
     * Filter and collect layout handles into array
     *
     * @param Mage_Core_Model_Layout_Element $layoutHandles
     */
    protected function _collectLayoutHandles($layoutHandles)
    {
        if ($layoutHandlesArr = $layoutHandles->xpath('/*/*/label/..')) {
            foreach ($layoutHandlesArr as $node) {
                if ($this->_filterLayoutHandle($node->getName())) {
                    $helper = Mage::helper(Mage_Core_Model_Layout::findTranslationModuleName($node));
                    $this->_layoutHandles[$node->getName()] = $this->helper('Mage_Core_Helper_Data')->jsQuoteEscape(
                        $helper->__((string)$node->label)
                    );
                }
            }
            asort($this->_layoutHandles, SORT_STRING);
        }
    }

    /**
     * Check if given layout handle allowed (do not match not allowed patterns)
     *
     * @param string $layoutHandle
     * @return boolean
     */
    protected function _filterLayoutHandle($layoutHandle)
    {
        $wildCard = '/('.implode(')|(', $this->getLayoutHandlePatterns()).')/';
        if (preg_match($wildCard, $layoutHandle)) {
            return false;
        }
        return true;
    }
}
