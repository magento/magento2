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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Review Edit Form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Review_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $review = Mage::registry('review_data');
        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($review->getEntityPkValue());
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($review->getCustomerId());

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'), 'ret' => Mage::registry('ret'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('review_details', array('legend' => Mage::helper('Mage_Review_Helper_Data')->__('Review Details'), 'class' => 'fieldset-wide'));

        /** @var $helper Mage_Review_Helper_Data */
        $helper = Mage::helper('Mage_Review_Helper_Data');
        $fieldset->addField('product_name', 'note', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Product'),
            'text'      => '<a href="' . $this->getUrl('*/catalog_product/edit', array('id' => $product->getId())) . '" onclick="this.target=\'blank\'">' . $helper->escapeHtml($product->getName()) . '</a>'
        ));

        if ($customer->getId()) {
            $customerText = Mage::helper('Mage_Review_Helper_Data')->__('<a href="%1$s" onclick="this.target=\'blank\'">%2$s %3$s</a> <a href="mailto:%4$s">(%4$s)</a>', $this->getUrl('*/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'review')), $this->escapeHtml($customer->getFirstname()), $this->escapeHtml($customer->getLastname()), $this->escapeHtml($customer->getEmail()));
        } else {
            if (is_null($review->getCustomerId())) {
                $customerText = Mage::helper('Mage_Review_Helper_Data')->__('Guest');
            } elseif ($review->getCustomerId() == 0) {
                $customerText = Mage::helper('Mage_Review_Helper_Data')->__('Administrator');
            }
        }

        $fieldset->addField('customer', 'note', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Posted By'),
            'text'      => $customerText,
        ));

        $fieldset->addField('summary_rating', 'note', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Summary Rating'),
            'text'      => $this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Rating_Summary')->toHtml(),
        ));

        $fieldset->addField('detailed_rating', 'note', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Detailed Rating'),
            'required'  => true,
            'text'      => '<div id="rating_detail">'
                           . $this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Rating_Detailed')->toHtml()
                           . '</div>',
        ));

        $fieldset->addField('status_id', 'select', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    => Mage::helper('Mage_Review_Helper_Data')->getReviewStatusesOptionArray(),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->hasSingleStore()) {
            $field = $fieldset->addField('select_stores', 'multiselect', array(
                'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Visible In'),
                'required'  => true,
                'name'      => 'stores[]',
                'values'    => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $field->setRenderer($renderer);
            $review->setSelectStores($review->getStores());
        }
        else {
            $fieldset->addField('select_stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $review->setSelectStores(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('nickname', 'text', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Nickname'),
            'required'  => true,
            'name'      => 'nickname'
        ));

        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Summary of Review'),
            'required'  => true,
            'name'      => 'title',
        ));

        $fieldset->addField('detail', 'textarea', array(
            'label'     => Mage::helper('Mage_Review_Helper_Data')->__('Review'),
            'required'  => true,
            'name'      => 'detail',
            'style'     => 'height:24em;',
        ));

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
