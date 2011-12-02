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
 * Widget Instance edit container
 *
 * @category    Mage
 * @package     Mage_Widget
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'instance_id';
        $this->_blockGroup = 'Mage_Widget';
        $this->_controller = 'adminhtml_widget_instance';
    }

    /**
     * Getter
     *
     * @return Mage_Widget_Model_Widget_Instance
     */
    public function getWidgetInstance()
    {
        return Mage::registry('current_widget_instance');
    }

    /**
     * Prepare layout.
     * Adding save_and_continue button
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit
     */
    protected function _preparelayout()
    {
        if ($this->getWidgetInstance()->isCompleteToCreate()) {
            $this->_addButton(
                'save_and_edit_button',
                array(
                    'label'     => Mage::helper('Mage_Widget_Helper_Data')->__('Save and Continue Edit'),
                    'class'     => 'save',
                    'onclick'   => 'saveAndContinueEdit()'
                ),
                100
            );
        } else {
            $this->removeButton('save');
        }
        return parent::_prepareLayout();
    }

    /**
     * Return translated header text depending on creating/editing action
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getWidgetInstance()->getId()) {
            return Mage::helper('Mage_Widget_Helper_Data')->__('Widget "%s"', $this->escapeHtml($this->getWidgetInstance()->getTitle()));
        }
        else {
            return Mage::helper('Mage_Widget_Helper_Data')->__('New Widget Instance');
        }
    }

    /**
     * Return validation url for edit form
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }
}
