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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 observer
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Observer
{
    /**
     * Save relation of admin user to API2 role
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function saveAdminToRoleRelation(Varien_Event_Observer $observer)
    {
        /** @var $user Mage_Admin_Model_User Object */
        $user = $observer->getObject();

        if ($user->hasData('api2_roles')) {
            $roles = $user->getData('api2_roles');

            if (!is_array($roles) || !isset($roles[0])) {
                throw new Exception('API2 roles property has wrong data format.');
            }

            /** @var $resourceModel Mage_Api2_Model_Resource_Acl_Global_Role */
            $resourceModel = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Role');
            $resourceModel->saveAdminToRoleRelation($user->getId(), $roles[0]);
        }
    }

    /**
     * After save attribute if it is not visible on front remove it from Attribute ACL
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Api2_Model_Observer
     */
    public function catalogAttributeSaveAfter(Varien_Event_Observer $observer)
    {
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->getIsUserDefined() && $attribute->dataHasChangedFor('is_visible_on_front')
            && !$attribute->getIsVisibleOnFront()) {
            /** @var $collection Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection */
            $collection = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Filter_Attribute_Collection');
            /** @var $aclFilter Mage_Api2_Model_Acl_Filter_Attribute */
            foreach ($collection as $aclFilter) {
                if ($aclFilter->getResourceId() != Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL) {
                    $allowedAttributes = explode(',', $aclFilter->getAllowedAttributes());
                    $allowedAttributes = array_diff($allowedAttributes, array($attribute->getAttributeCode()));
                    $aclFilter->setAllowedAttributes(implode(',', $allowedAttributes))->save();
                }
            }
        }

        return $this;
    }
}
