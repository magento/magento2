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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Assign order status to order state form
 */
class Mage_Adminhtml_Block_Sales_Order_Status_Assign_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_status_state');
    }

    /**
     * Prepare form fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('Mage_Sales_Helper_Data')->__('Assignment Information')
        ));

        $statuses = Mage::getResourceModel('Mage_Sales_Model_Resource_Order_Status_Collection')
            ->toOptionArray();
        array_unshift($statuses, array('value' => '', 'label' => ''));

        $states = Mage::getSingleton('Mage_Sales_Model_Order_Config')->getStates();
        $states = array_merge(array('' => ''), $states);

        $fieldset->addField('status', 'select',
            array(
                'name'      => 'status',
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Order Status'),
                'class'     => 'required-entry',
                'values'    => $statuses,
                'required'  => true,
            )
        );

        $fieldset->addField('state', 'select',
            array(
                'name'      => 'state',
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Order State'),
                'class'     => 'required-entry',
                'values'    => $states,
                'required'  => true,
            )
        );

        $fieldset->addField('is_default', 'checkbox',
            array(
                'name'      => 'is_default',
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Use Order Status As Default'),
                'value'     => 1,
            )
        );


        $form->setAction($this->getUrl('*/sales_order_status/assignPost'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
