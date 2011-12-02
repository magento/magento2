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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin tag edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Tag_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Add and update buttons
     *
     * @return void
     */
    public function __construct()
    {
        $this->_objectId   = 'tag_id';
        $this->_controller = 'tag';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('Mage_Tag_Helper_Data')->__('Save Tag'));
        $this->_updateButton('delete', 'label', Mage::helper('Mage_Tag_Helper_Data')->__('Delete Tag'));

        if (!Mage::registry('current_tag')) {
            return;
        }

        $this->addButton('save_and_edit_button', array(
            'label'   => Mage::helper('Mage_Tag_Helper_Data')->__('Save and Continue Edit'),
            'onclick' => "saveAndContinueEdit('" . $this->getSaveAndContinueUrl() . "')",
            'class'   => 'save'
        ), 1);
    }

    /**
     * Add child HTML to layout
     *
     * @return Mage_Adminhtml_Block_Tag_Edit
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild(
                'store_switcher',
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tag_Store_Switcher'))
             ->setChild(
                'tag_assign_accordion',
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tag_Edit_Assigned'))
             ->setChild(
                'accordion',
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tag_Edit_Accordion'));

        return $this;
    }

    /**
     * Retrieve Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_tag')->getId()) {
            return Mage::helper('Mage_Tag_Helper_Data')->__("Edit Tag '%s'", $this->escapeHtml(Mage::registry('current_tag')->getName()));
        }
        return Mage::helper('Mage_Tag_Helper_Data')->__('New Tag');
    }

    /**
     * Retrieve Accordions HTML
     *
     * @return string
     */
    public function getAcordionsHtml()
    {
        return $this->getChildHtml('accordion');
    }

    /**
     * Retrieve Tag Delete URL
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('tag_id' => $this->getRequest()->getParam($this->_objectId), 'ret' => $this->getRequest()->getParam('ret', 'index')));
    }

    /**
     * Retrieve Assigned Tags Accordion HTML
     *
     * @return string
     */
    public function getTagAssignAccordionHtml()
    {
        return $this->getChildHtml('tag_assign_accordion');
    }

    /**
     * Retrieve Store Switcher HTML
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }

    /**
     * Retrieve Tag Save URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    /**
     * Retrieve Tag SaveAndContinue URL
     *
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'ret' => 'edit', 'continue' => $this->getRequest()->getParam('ret', 'index'), 'store' => Mage::registry('current_tag')->getStoreId()));
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
}
