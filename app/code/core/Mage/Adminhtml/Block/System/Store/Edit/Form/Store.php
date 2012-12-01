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
 * Adminhtml store edit form for store
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Store_Edit_Form_Store extends Mage_Adminhtml_Block_System_Store_Edit_FormAbstract
{
    /**
     * Prepare store specific fieldset
     *
     * @param Varien_Data_Form $form
     */
    protected function _prepareStoreFieldset(Varien_Data_Form $form)
    {
        $storeModel = Mage::registry('store_data');
        if ($postData = Mage::registry('store_post_data')) {
            $storeModel->setData($postData['store']);
        }
        $fieldset = $form->addFieldset('store_fieldset', array(
            'legend' => Mage::helper('Mage_Core_Helper_Data')->__('Store View Information')
        ));

        if (Mage::registry('store_action') == 'edit' || Mage::registry('store_action') == 'add' ) {
            $fieldset->addField('store_group_id', 'select', array(
                'name'      => 'store[group_id]',
                'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Store'),
                'value'     => $storeModel->getGroupId(),
                'values'    => $this->_getStoreGroups(),
                'required'  => true,
                'disabled'  => $storeModel->isReadOnly(),
            ));
            if ($storeModel->getId() && $storeModel->getGroup()->getDefaultStoreId() == $storeModel->getId()) {
                if ($storeModel->getGroup() && $storeModel->getGroup()->getStoresCount() > 1) {
                    $form->getElement('store_group_id')->setDisabled(true);

                    $fieldset->addField('store_hidden_group_id', 'hidden', array(
                        'name'      => 'store[group_id]',
                        'no_span'   => true,
                        'value'     => $storeModel->getGroupId()
                    ));
                } else {
                    $fieldset->addField('store_original_group_id', 'hidden', array(
                        'name'      => 'store[original_group_id]',
                        'no_span'   => true,
                        'value'     => $storeModel->getGroupId()
                    ));
                }
            }
        }

        $fieldset->addField('store_name', 'text', array(
            'name'      => 'store[name]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Name'),
            'value'     => $storeModel->getName(),
            'required'  => true,
            'disabled'  => $storeModel->isReadOnly(),
        ));
        $fieldset->addField('store_code', 'text', array(
            'name'      => 'store[code]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Code'),
            'value'     => $storeModel->getCode(),
            'required'  => true,
            'disabled'  => $storeModel->isReadOnly(),
        ));

        $fieldset->addField('store_is_active', 'select', array(
            'name'      => 'store[is_active]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Status'),
            'value'     => $storeModel->getIsActive(),
            'options'   => array(
                0 => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Disabled'),
                1 => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Enabled')),
            'required'  => true,
            'disabled'  => $storeModel->isReadOnly(),
        ));

        $fieldset->addField('store_sort_order', 'text', array(
            'name'      => 'store[sort_order]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Sort Order'),
            'value'     => $storeModel->getSortOrder(),
            'required'  => false,
            'disabled'  => $storeModel->isReadOnly(),
        ));

        $fieldset->addField('store_is_default', 'hidden', array(
            'name'      => 'store[is_default]',
            'no_span'   => true,
            'value'     => $storeModel->getIsDefault(),
        ));

        $fieldset->addField('store_store_id', 'hidden', array(
            'name'      => 'store[store_id]',
            'no_span'   => true,
            'value'     => $storeModel->getId(),
            'disabled'  => $storeModel->isReadOnly(),
        ));
    }

    /**
     * Retrieve list of store groups
     *
     * @return array
     */
    protected function _getStoreGroups()
    {
        $websites = Mage::getModel('Mage_Core_Model_Website')->getCollection();
        $allgroups = Mage::getModel('Mage_Core_Model_Store_Group')->getCollection();
        $groups = array();
        foreach ($websites as $website) {
            $values = array();
            foreach ($allgroups as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $values[] = array('label'=>$group->getName(),'value'=>$group->getId());
                }
            }
            $groups[] = array('label'=>$website->getName(),'value'=>$values);
        }
        return $groups;
    }
}
