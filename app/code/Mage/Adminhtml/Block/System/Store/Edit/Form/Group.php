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
 * Adminhtml store edit form for group
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Store_Edit_Form_Group extends Mage_Adminhtml_Block_System_Store_Edit_FormAbstract
{
    /**
     * Prepare group specific fieldset
     *
     * @param Varien_Data_Form $form
     */
    protected function _prepareStoreFieldset(Varien_Data_Form $form)
    {
        $groupModel = Mage::registry('store_data');
        if ($postData = Mage::registry('store_post_data')) {
            $groupModel->setData($postData['group']);
        }

        $fieldset = $form->addFieldset('group_fieldset', array(
            'legend' => Mage::helper('Mage_Core_Helper_Data')->__('Store Information')
        ));

        if (Mage::registry('store_action') == 'edit' || Mage::registry('store_action') == 'add') {
            $websites = Mage::getModel('Mage_Core_Model_Website')->getCollection()->toOptionArray();
            $fieldset->addField('group_website_id', 'select', array(
                'name'      => 'group[website_id]',
                'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Web Site'),
                'value'     => $groupModel->getWebsiteId(),
                'values'    => $websites,
                'required'  => true,
                'disabled'  => $groupModel->isReadOnly(),
            ));

            if ($groupModel->getId() && $groupModel->getWebsite()->getDefaultGroupId() == $groupModel->getId()) {
                if ($groupModel->getWebsite()->getIsDefault() || $groupModel->getWebsite()->getGroupsCount() == 1) {
                    $form->getElement('group_website_id')->setDisabled(true);

                    $fieldset->addField('group_hidden_website_id', 'hidden', array(
                        'name'      => 'group[website_id]',
                        'no_span'   => true,
                        'value'     => $groupModel->getWebsiteId()
                    ));
                } else {
                    $fieldset->addField('group_original_website_id', 'hidden', array(
                        'name'      => 'group[original_website_id]',
                        'no_span'   => true,
                        'value'     => $groupModel->getWebsiteId()
                    ));
                }
            }
        }

        $fieldset->addField('group_name', 'text', array(
            'name'      => 'group[name]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Name'),
            'value'     => $groupModel->getName(),
            'required'  => true,
            'disabled'  => $groupModel->isReadOnly(),
        ));

        $categories = Mage::getModel('Mage_Catalog_Model_Config_Source_Category')->toOptionArray();

        $fieldset->addField('group_root_category_id', 'select', array(
            'name'      => 'group[root_category_id]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Root Category'),
            'value'     => $groupModel->getRootCategoryId(),
            'values'    => $categories,
            'required'  => true,
            'disabled'  => $groupModel->isReadOnly(),
        ));

        if (Mage::registry('store_action') == 'edit') {
            $stores = Mage::getModel('Mage_Core_Model_Store')->getCollection()
                ->addGroupFilter($groupModel->getId())->toOptionArray();
            $fieldset->addField('group_default_store_id', 'select', array(
                'name'      => 'group[default_store_id]',
                'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Default Store View'),
                'value'     => $groupModel->getDefaultStoreId(),
                'values'    => $stores,
                'required'  => false,
                'disabled'  => $groupModel->isReadOnly(),
            ));
        }

        $fieldset->addField('group_group_id', 'hidden', array(
            'name'      => 'group[group_id]',
            'no_span'   => true,
            'value'     => $groupModel->getId()
        ));
    }
}
