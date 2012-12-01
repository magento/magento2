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
 * Product attribute add/edit form system tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_System extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_attribute');

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('Mage_Catalog_Helper_Data')->__('System Properties')));

        if ($model->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', array(
                'name' => 'attribute_id',
            ));
        }

        $yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Yes')
            ));

        /*$fieldset->addField('attribute_model', 'text', array(
            'name' => 'attribute_model',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Model'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Model'),
        ));

        $fieldset->addField('backend_model', 'text', array(
            'name' => 'backend_model',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Backend Model'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Backend Model'),
        ));*/

        $fieldset->addField('backend_type', 'select', array(
            'name' => 'backend_type',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Data Type for Saving in Database'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Data Type for Saving in Database'),
            'options' => array(
                'text'      => Mage::helper('Mage_Catalog_Helper_Data')->__('Text'),
                'varchar'   => Mage::helper('Mage_Catalog_Helper_Data')->__('Varchar'),
                'static'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Static'),
                'datetime'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Datetime'),
                'decimal'   => Mage::helper('Mage_Catalog_Helper_Data')->__('Decimal'),
                'int'       => Mage::helper('Mage_Catalog_Helper_Data')->__('Integer'),
            ),
        ));

        /*$fieldset->addField('backend_table', 'text', array(
            'name' => 'backend_table',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Backend Table'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Backend Table Title'),
        ));

        $fieldset->addField('frontend_model', 'text', array(
            'name' => 'frontend_model',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Frontend Model'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Frontend Model'),
        ));*/

        /*$fieldset->addField('is_visible', 'select', array(
            'name' => 'is_visible',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Visible'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Visible'),
            'values' => $yesno,
        ));*/

        /*$fieldset->addField('source_model', 'text', array(
            'name' => 'source_model',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Source Model'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Source Model'),
        ));*/

        $fieldset->addField('is_global', 'select', array(
            'name'  => 'is_global',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Globally Editable'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Globally Editable'),
            'values'=> $yesno,
        ));

        $form->setValues($model->getData());

        if ($model->getAttributeId()) {
            $form->getElement('backend_type')->setDisabled(1);
            if ($model->getIsGlobal()) {
                #$form->getElement('is_global')->setDisabled(1);
            }
        } else {
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
