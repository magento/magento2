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
 * Adminhtml store edit form for website
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Store_Edit_Form_Website extends Mage_Adminhtml_Block_System_Store_Edit_FormAbstract
{
    /**
     * Prepare website specific fieldset
     *
     * @param Varien_Data_Form $form
     */
    protected function _prepareStoreFieldset(Varien_Data_Form $form)
    {
        $websiteModel = Mage::registry('store_data');
        if ($postData = Mage::registry('store_post_data')) {
            $websiteModel->setData($postData['website']);
        }
        $fieldset = $form->addFieldset('website_fieldset', array(
            'legend' => Mage::helper('Mage_Core_Helper_Data')->__('Website Information')
        ));
        /* @var $fieldset Varien_Data_Form */

        $fieldset->addField('website_name', 'text', array(
            'name'      => 'website[name]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Name'),
            'value'     => $websiteModel->getName(),
            'required'  => true,
            'disabled'  => $websiteModel->isReadOnly(),
        ));

        $fieldset->addField('website_code', 'text', array(
            'name'      => 'website[code]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Code'),
            'value'     => $websiteModel->getCode(),
            'required'  => true,
            'disabled'  => $websiteModel->isReadOnly(),
        ));

        $fieldset->addField('website_sort_order', 'text', array(
            'name'      => 'website[sort_order]',
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Sort Order'),
            'value'     => $websiteModel->getSortOrder(),
            'required'  => false,
            'disabled'  => $websiteModel->isReadOnly(),
        ));

        if (Mage::registry('store_action') == 'edit') {
            $groups = Mage::getModel('Mage_Core_Model_Store_Group')->getCollection()
                ->addWebsiteFilter($websiteModel->getId())
                ->setWithoutStoreViewFilter()
                ->toOptionArray();

            $fieldset->addField('website_default_group_id', 'select', array(
                'name'      => 'website[default_group_id]',
                'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Default Store'),
                'value'     => $websiteModel->getDefaultGroupId(),
                'values'    => $groups,
                'required'  => false,
                'disabled'  => $websiteModel->isReadOnly(),
            ));
        }

        if (!$websiteModel->getIsDefault() && $websiteModel->getStoresCount()) {
            $fieldset->addField('is_default', 'checkbox', array(
                'name'      => 'website[is_default]',
                'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Set as Default'),
                'value'     => 1,
                'disabled'  => $websiteModel->isReadOnly(),
            ));
        } else {
            $fieldset->addField('is_default', 'hidden', array(
                'name'      => 'website[is_default]',
                'value'     => $websiteModel->getIsDefault()
            ));
        }

        $fieldset->addField('website_website_id', 'hidden', array(
            'name'  => 'website[website_id]',
            'value' => $websiteModel->getId()
        ));
    }
}
