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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product attribute set api
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Set_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve attribute set list
     *
     * @return array
     */
    public function items()
    {
        $entityType = Mage::getModel('Mage_Catalog_Model_Product')->getResource()->getEntityType();
        $collection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter($entityType->getId());

        $result = array();
        foreach ($collection as $attributeSet) {
            $result[] = array(
                'set_id' => $attributeSet->getId(),
                'name'   => $attributeSet->getAttributeSetName()
            );

        }

        return $result;
    }

    /**
     * Create new attribute set based on another set
     *
     * @param string $attributeSetName
     * @param string $skeletonSetId
     * @return integer
     */
    public function create($attributeSetName, $skeletonSetId)
    {
        // check if set with requested $skeletonSetId exists
        if (!Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')->load($skeletonSetId)->getId()){
            $this->_fault('invalid_skeleton_set_id');
        }
        // get catalog product entity type id
        $entityTypeId = Mage::getModel('Mage_Catalog_Model_Product')->getResource()->getTypeId();
        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')
                ->setEntityTypeId($entityTypeId)
                ->setAttributeSetName($attributeSetName);
        try {
            // check if name is valid
            $attributeSet->validate();
            // copy parameters to new set from skeleton set
            $attributeSet->save();
            $attributeSet->initFromSkeleton($skeletonSetId)->save();
        } catch (Mage_Eav_Exception $e) {
            $this->_fault('invalid_data', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('create_attribute_set_error', $e->getMessage());
        }
        return (int)$attributeSet->getId();
    }

    /**
     * Remove attribute set
     *
     * @param string $attributeSetId
     * @param bool $forceProductsRemove
     * @return bool
     */
    public function remove($attributeSetId, $forceProductsRemove = false)
    {
        // if attribute set has related goods and $forceProductsRemove is not set throw exception
        if (!$forceProductsRemove) {
            /** @var $catalogProductsCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
            $catalogProductsCollection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection()
                    ->addFieldToFilter('attribute_set_id', $attributeSetId);
            if (count($catalogProductsCollection)) {
                $this->_fault('attribute_set_has_related_products');
            }
        }
        $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')->load($attributeSetId);
        // check if set with requested id exists
        if (!$attributeSet->getId()){
            $this->_fault('invalid_attribute_set_id');
        }
        try {
            $attributeSet->delete();
        } catch (Exception $e) {
            $this->_fault('remove_attribute_set_error', $e->getMessage());
        }
        return true;
    }

    /**
     * Add attribute to attribute set
     *
     * @param string $attributeId
     * @param string $attributeSetId
     * @param string|null $attributeGroupId
     * @param string $sortOrder
     * @return bool
     */
    public function attributeAdd($attributeId, $attributeSetId, $attributeGroupId = null, $sortOrder = '0')
    {
        // check if attribute with requested id exists
        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = Mage::getModel('Mage_Eav_Model_Entity_Attribute')->load($attributeId);
        if (!$attribute->getId()) {
            $this->_fault('invalid_attribute_id');
        }
        // check if attribute set with requested id exists
        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')->load($attributeSetId);
        if (!$attributeSet->getId()) {
            $this->_fault('invalid_attribute_set_id');
        }
        if (!empty($attributeGroupId)) {
            // check if attribute group with requested id exists
            if (!Mage::getModel('Mage_Eav_Model_Entity_Attribute_Group')->load($attributeGroupId)->getId()) {
                $this->_fault('invalid_attribute_group_id');
            }
        } else {
            // define default attribute group id for current attribute set
            $attributeGroupId = $attributeSet->getDefaultGroupId();
        }
        $attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();
        if ($attribute->getEntityAttributeId()) {
            $this->_fault('attribute_is_already_in_set');
        }
        try {
           $attribute->setEntityTypeId($attributeSet->getEntityTypeId())
                    ->setAttributeSetId($attributeSetId)
                    ->setAttributeGroupId($attributeGroupId)
                    ->setSortOrder($sortOrder)
                    ->save();
        } catch (Exception $e) {
            $this->_fault('add_attribute_error', $e->getMessage());
        }
        return true;
    }

    /**
     * Remove attribute from attribute set
     *
     * @param string $attributeId
     * @param string $attributeSetId
     * @return bool
     */
    public function attributeRemove($attributeId, $attributeSetId)
    {
        // check if attribute with requested id exists
        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = Mage::getModel('Mage_Eav_Model_Entity_Attribute')->load($attributeId);
        if (!$attribute->getId()) {
            $this->_fault('invalid_attribute_id');
        }
        // check if attribute set with requested id exists
        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')->load($attributeSetId);
        if (!$attributeSet->getId()) {
            $this->_fault('invalid_attribute_set_id');
        }
        // check if attribute is in set
        $attribute->setAttributeSetId($attributeSet->getId())->loadEntityAttributeIdBySet();
        if (!$attribute->getEntityAttributeId()) {
            $this->_fault('attribute_is_not_in_set');
        }
        try {
            // delete record from eav_entity_attribute
            // using entity_attribute_id loaded by loadEntityAttributeIdBySet()
            $attribute->deleteEntity();
        } catch (Exception $e) {
            $this->_fault('remove_attribute_error', $e->getMessage());
        }

        return true;
    }

    /**
     * Create group within existing attribute set
     *
     * @param  string|int $attributeSetId
     * @param  string $groupName
     * @return int
     */
    public function groupAdd($attributeSetId, $groupName)
    {
        /** @var $group Mage_Eav_Model_Entity_Attribute_Group */
        $group = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Group');
        $group->setAttributeSetId($attributeSetId)
                ->setAttributeGroupName(
                    Mage::helper('Mage_Catalog_Helper_Data')->stripTags($groupName)
                );
        if ($group->itemExists()) {
            $this->_fault('group_already_exists');
        }
        try {
            $group->save();
        } catch (Exception $e) {
            $this->_fault('group_add_error', $e->getMessage());
        }
        return (int)$group->getId();
    }

    /**
     * Rename existing group
     *
     * @param string|int $groupId
     * @param string $groupName
     * @return boolean
     */
    public function groupRename($groupId, $groupName)
    {
        $model = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Group')->load($groupId);

        if (!$model->getAttributeGroupName()) {
            $this->_fault('invalid_attribute_group_id');
        }

        $model->setAttributeGroupName(
                Mage::helper('Mage_Catalog_Helper_Data')->stripTags($groupName)
        );
        try {
            $model->save();
        } catch (Exception $e) {
            $this->_fault('group_rename_error', $e->getMessage());
        }
        return true;
    }

    /**
        * Remove group from existing attribute set
        *
        * @param  string|int $attributeGroupId
        * @return bool
        */
       public function groupRemove($attributeGroupId)
       {
           /** @var $group Mage_Catalog_Model_Product_Attribute_Group */
           $group = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Group')->load($attributeGroupId);
           if (!$group->getId()) {
               $this->_fault('invalid_attribute_group_id');
           }
           if ($group->hasConfigurableAttributes()) {
               $this->_fault('group_has_configurable_attributes');
           }
           if ($group->hasSystemAttributes()) {
               $this->_fault('group_has_system_attributes');
           }
           try {
               $group->delete();
           } catch (Exception $e) {
               $this->_fault('group_remove_error', $e->getMessage());
           }
           return true;
       }

} // Class Mage_Catalog_Model_Product_Attribute_Set_Api End
