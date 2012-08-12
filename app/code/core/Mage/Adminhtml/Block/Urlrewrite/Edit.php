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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for URL rewrites edit page
 *
 * @method Mage_Core_Model_Url_Rewrite getUrlRewrite()
 * @method Mage_Adminhtml_Block_Urlrewrite_Edit setUrlRewrite(Mage_Core_Model_Url_Rewrite $urlRewrite)
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Urlrewrite_Edit extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * @var Mage_Adminhtml_Block_Urlrewrite_Selector
     */
    private $_selectorBlock;

    /**
     * Part for building some blocks names
     *
     * @var string
     */
    protected $_controller = 'urlrewrite';

    /**
     * Generated buttons html cache
     *
     * @var string
     */
    protected $_buttonsHtml;

    /**
     * Prepare URL rewrite editing layout
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Edit
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('urlrewrite/edit.phtml');

        $this->_addBackButton();
        $this->_prepareLayoutFeatures();

        return parent::_prepareLayout();
    }

    /**
     * Prepare featured blocks for layout of URL rewrite editing
     */
    protected function _prepareLayoutFeatures()
    {
        /** @var $helper Mage_Adminhtml_Helper_Data */
        $helper = Mage::helper('Mage_Adminhtml_Helper_Data');

        if ($this->_getUrlRewrite()->getId()) {
            $this->_headerText = Mage::helper('Mage_Adminhtml_Helper_Data')->__('Edit URL Rewrite');
        } else {
            $this->_headerText = Mage::helper('Mage_Adminhtml_Helper_Data')->__('Add New URL Rewrite');
        }

        $this->_updateBackButtonLink($helper->getUrl('*/*/edit') . $this->_getSelectorBlock()->getDefaultMode());
        $this->_addUrlRewriteSelectorBlock();
        $this->_addEditFormBlock();
    }

    /**
     * Add child edit form block
     */
    protected function _addEditFormBlock()
    {
        $this->setChild('form', $this->_createEditFormBlock());

        if ($this->_getUrlRewrite()->getId()) {
            $this->_addResetButton();
            $this->_addDeleteButton();
        }

        $this->_addSaveButton();
    }

    /**
     * Add reset button
     */
    protected function _addResetButton()
    {
        $this->_addButton('reset', array(
            'label'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Reset'),
            'onclick' => '$(\'edit_form\').reset()',
            'class'   => 'scalable',
            'level'   => -1
        ));
    }

    /**
     * Add back button
     */
    protected function _addBackButton()
    {
        /** @var $helper Mage_Adminhtml_Helper_Data */
        $helper = Mage::helper('Mage_Adminhtml_Helper_Data');

        $this->_addButton('back', array(
            'label'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Back'),
            'onclick' => 'setLocation(\'' . $helper->getUrl('*/*/') . '\')',
            'class'   => 'back',
            'level'   => -1
        ));
    }

    /**
     * Update Back button location link
     *
     * @param string $link
     */
    protected function _updateBackButtonLink($link)
    {
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $link . '\')');
    }

    /**
     * Add delete button
     */
    protected function _addDeleteButton()
    {
        /** @var $helper Mage_Adminhtml_Helper_Data */
        $helper = Mage::helper('Mage_Adminhtml_Helper_Data');

        $this->_addButton('delete', array(
            'label'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Delete'),
            'onclick' => 'deleteConfirm(\''
                . addslashes(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Are you sure you want to do this?'))
                . '\', \'' . $helper->getUrl('*/*/delete', array('id' => $this->getUrlRewrite()->getId())) . '\')',
            'class'   => 'scalable delete',
            'level'   => -1
        ));
    }

    /**
     * Add save button
     */
    protected function _addSaveButton()
    {
        $this->_addButton('save', array(
            'label'   => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Save'),
            'onclick' => 'editForm.submit()',
            'class'   => 'save',
            'level'   => -1
        ));
    }

    /**
     * Creates edit form block
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Edit_Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite_Edit_Form', '', array(
            'url_rewrite' => $this->_getUrlRewrite()
        ));
    }

    /**
     * Add child URL rewrite selector block
     */
    protected function _addUrlRewriteSelectorBlock()
    {
        $this->setChild('selector', $this->_getSelectorBlock());
    }

    /**
     * Get selector block
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Selector
     */
    private function _getSelectorBlock()
    {
        if (!$this->_selectorBlock) {
            $this->_selectorBlock = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Urlrewrite_Selector');
        }
        return $this->_selectorBlock;
    }

    /**
     * Get container buttons HTML
     *
     * Since buttons are set as children, we remove them as children after generating them
     * not to duplicate them in future
     *
     * @param null $area
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getButtonsHtml($area = null)
    {
        if (null === $this->_buttonsHtml) {
            $this->_buttonsHtml = parent::getButtonsHtml();
            $layout = $this->getLayout();
            foreach ($this->getChildNames() as $name) {
                $alias = $layout->getElementAlias($name);
                if (false !== strpos($alias, '_button')) {
                    $layout->unsetChild($this->getNameInLayout(), $alias);
                }
            }
        }
        return $this->_buttonsHtml;
    }

    /**
     * Get or create new instance of URL rewrite
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    protected function _getUrlRewrite()
    {
        if (!$this->hasData('url_rewrite')) {
            $this->setUrlRewrite(Mage::getModel('Mage_Core_Model_Url_Rewrite'));
        }
        return $this->getUrlRewrite();
    }
}
