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
class Mage_Adminhtml_Block_System_Design_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('general', array('legend'=>Mage::helper('Mage_Core_Helper_Data')->__('General Settings')));

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'select', array(
                'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Store'),
                'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Store'),
                'values'   => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(),
                'name'     => 'store_id',
                'required' => true,
            ));
            $renderer = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
        }

        $fieldset->addField('design', 'select', array(
            'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Custom Design'),
            'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Custom Design'),
            'values'   => Mage::getSingleton('Mage_Core_Model_Design_Source_Design')->getAllOptions(),
            'name'     => 'design',
            'required' => true,
        ));

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('date_from', 'date', array(
            'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Date From'),
            'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Date From'),
            'name'     => 'date_from',
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'date_format' => $dateFormat,
            //'required' => true,
        ));
        $fieldset->addField('date_to', 'date', array(
            'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Date To'),
            'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Date To'),
            'name'     => 'date_to',
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'date_format' => $dateFormat,
            //'required' => true,
        ));

        $formData = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getDesignData(true);
        if (!$formData){
            $formData = Mage::registry('design')->getData();
        } else {
            $formData = $formData['design'];
        }

        $form->addValues($formData);
        $form->setFieldNameSuffix('design');
        $this->setForm($form);
    }

}
