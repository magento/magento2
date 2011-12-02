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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Adminhtml report filter form
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Block_Adminhtml_Report_Filter_Form extends Mage_Adminhtml_Block_Report_Filter_Form
{
    /**
     * Add fields to base fieldset which are general to sales reports
     *
     * @return Mage_Sales_Block_Adminhtml_Report_Filter_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {

            $statuses = Mage::getModel('Mage_Sales_Model_Order_Config')->getStatuses();
            $values = array();
            foreach ($statuses as $code => $label) {
                if (false === strpos($code, 'pending')) {
                    $values[] = array(
                        'label' => Mage::helper('Mage_Reports_Helper_Data')->__($label),
                        'value' => $code
                    );
                }
            }

            $fieldset->addField('show_order_statuses', 'select', array(
                'name'      => 'show_order_statuses',
                'label'     => Mage::helper('Mage_Reports_Helper_Data')->__('Order Status'),
                'options'   => array(
                        '0' => Mage::helper('Mage_Reports_Helper_Data')->__('Any'),
                        '1' => Mage::helper('Mage_Reports_Helper_Data')->__('Specified'),
                    ),
                'note'      => Mage::helper('Mage_Reports_Helper_Data')->__('Applies to Any of the Specified Order Statuses'),
            ), 'to');

            $fieldset->addField('order_statuses', 'multiselect', array(
                'name'      => 'order_statuses',
                'values'    => $values,
                'display'   => 'none'
            ), 'show_order_statuses');

            // define field dependencies
            if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
                $this->setChild(
                    'form_after',
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
                        ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                        ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                        ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
                );
            }
        }

        return $this;
    }
}
