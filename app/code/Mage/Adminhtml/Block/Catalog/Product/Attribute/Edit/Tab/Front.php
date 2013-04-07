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
 * Product attribute add/edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Front extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_attribute');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('Mage_Catalog_Helper_Data')->__('Frontend Properties')));

        $yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Yes')
            ));


        $fieldset->addField('is_searchable', 'select', array(
            'name' => 'is_searchable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Quick Search'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Quick Search'),
            'values' => $yesno,
        ));

        $fieldset->addField('is_visible_in_advanced_search', 'select', array(
            'name' => 'is_visible_in_advanced_search',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Advanced Search'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use in Advanced Search'),
            'values' => $yesno,
        ));

        $fieldset->addField('is_comparable', 'select', array(
            'name' => 'is_comparable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Comparable on the Frontend'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Comparable on the Frontend'),
            'values' => $yesno,
        ));


        $fieldset->addField('is_filterable', 'select', array(
            'name' => 'is_filterable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__("Use in Layered Navigation<br/>(Can be used only with catalog input type 'Dropdown')"),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Can be used only with catalog input type Dropdown'),
            'values' => array(
                array('value' => '0', 'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('No')),
                array('value' => '1', 'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Filterable (with results)')),
                array('value' => '2', 'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Filterable (no results)')),
            ),
        ));

//        if ($model->getIsUserDefined() || !$model->getId()) {
            $fieldset->addField('is_visible_on_front', 'select', array(
                'name' => 'is_visible_on_front',
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Visible on Catalog Pages on Front-end'),
                'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Visible on Catalog Pages on Front-end'),
                'values' => $yesno,
            ));
//        }

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
