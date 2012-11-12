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
 * Create order status form
 */
class Mage_Adminhtml_Block_Sales_Order_Status_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('new_order_status');
    }

    /**
     * Prepare form fields and structure
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_status');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('Mage_Sales_Helper_Data')->__('Order Status Information')
        ));

        $fieldset->addField('is_new', 'hidden', array('name' => 'is_new', 'value' => 1));

        $fieldset->addField('status', 'text',
            array(
                'name' => 'status',
                'label' => Mage::helper('Mage_Sales_Helper_Data')->__('Status Code'),
                'class' => 'required-entry validate-code',
                'required' => true,
            )
        );

        $fieldset->addField('label', 'text',
            array(
                'name' => 'label',
                'label' => Mage::helper('Mage_Sales_Helper_Data')->__('Status Label'),
                'class' => 'required-entry',
                'required' => true,
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->_addStoresFieldset($model, $form);
        }

        if ($model) {
            $form->addValues($model->getData());
        }
        $form->setAction($this->getUrl('*/sales_order_status/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add Fieldset with Store labels
     *
     * @param Mage_Sales_Model_Order_Status $model
     * @param Varien_Data_Form $form
     */
    protected function _addStoresFieldset($model, $form)
    {
        $labels = $model ? $model->getStoreLabels() : array();
        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend' => Mage::helper('Mage_Sales_Helper_Data')->__('Store View Specific Labels'),
            'table_class' => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset');
        $fieldset->setRenderer($renderer);

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label' => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label' => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("store_label_{$store->getId()}", 'text', array(
                        'name' => 'store_labels[' . $store->getId() . ']',
                        'required' => false,
                        'label' => $store->getName(),
                        'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }
    }
}
