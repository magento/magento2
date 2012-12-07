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
 * New category creation form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_NewCategory extends Mage_Backend_Block_Widget_Form
{
    /**
     * Form preparation
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('new_category_messages', 'note', array());

        $fieldset = $form->addFieldset('new_category_form', array());

        $fieldset->addField('new_category_name', 'text', array(
            'label'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Category Name'),
            'title'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Category Name'),
            'required' => true,
        ));

        $fieldset->addField('new_category_parent', 'text', array(
            'label'        => Mage::helper('Mage_Catalog_Helper_Data')->__('Parent Category'),
            'title'        => Mage::helper('Mage_Catalog_Helper_Data')->__('Parent Category'),
            'autocomplete' => 'off',
            'required'     => true,
            'class'        => 'validate-parent-category',
        ));

        $fieldset->addField('new_category_parent_id', 'hidden', array());

        $this->setForm($form);
    }

    /**
     * Category save action URL
     *
     * @return string
     */
    public function getSaveCategoryUrl()
    {
        return $this->getUrl('*/catalog_category/save');
    }

    /**
     * Category suggestion action URL
     *
     * @return string
     */
    public function getSuggestCategoryUrl()
    {
        return $this->getUrl('*/catalog_category/suggestCategories');
    }
}
