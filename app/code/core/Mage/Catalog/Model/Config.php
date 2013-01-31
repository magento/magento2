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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Catalog_Model_Config extends Mage_Eav_Model_Config
{
    const XML_PATH_LIST_DEFAULT_SORT_BY     = 'catalog/frontend/default_sort_by';

    protected $_attributeSetsById;
    protected $_attributeSetsByName;

    protected $_attributeGroupsById;
    protected $_attributeGroupsByName;

    protected $_productTypesById;

    /**
     * Array of attributes codes needed for product load
     *
     * @var array
     */
    protected $_productAttributes;

    /**
     * Product Attributes used in product listing
     *
     * @var array
     */
    protected $_usedInProductListing;

    /**
     * Product Attributes For Sort By
     *
     * @var array
     */
    protected $_usedForSortBy;

    protected $_storeId = null;

    const XML_PATH_PRODUCT_COLLECTION_ATTRIBUTES = 'frontend/product/collection/attributes';

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Catalog_Model_Resource_Config');
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id, if is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    public function loadAttributeSets()
    {
        if ($this->_attributeSetsById) {
            return $this;
        }

        $attributeSetCollection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->load();

        $this->_attributeSetsById = array();
        $this->_attributeSetsByName = array();
        foreach ($attributeSetCollection as $id=>$attributeSet) {
            $entityTypeId = $attributeSet->getEntityTypeId();
            $name = $attributeSet->getAttributeSetName();
            $this->_attributeSetsById[$entityTypeId][$id] = $name;
            $this->_attributeSetsByName[$entityTypeId][strtolower($name)] = $id;
        }
        return $this;
    }

    public function getAttributeSetName($entityTypeId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        return isset($this->_attributeSetsById[$entityTypeId][$id]) ? $this->_attributeSetsById[$entityTypeId][$id] : false;
    }

    public function getAttributeSetId($entityTypeId, $name)
    {
        if (is_numeric($name)) {
            return $name;
        }
        $this->loadAttributeSets();

        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId)->getId();
        }
        $name = strtolower($name);
        return isset($this->_attributeSetsByName[$entityTypeId][$name]) ? $this->_attributeSetsByName[$entityTypeId][$name] : false;
    }

    public function loadAttributeGroups()
    {
        if ($this->_attributeGroupsById) {
            return $this;
        }

        $attributeSetCollection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection')
            ->load();

        $this->_attributeGroupsById = array();
        $this->_attributeGroupsByName = array();
        foreach ($attributeSetCollection as $id=>$attributeGroup) {
            $attributeSetId = $attributeGroup->getAttributeSetId();
            $name = $attributeGroup->getAttributeGroupName();
            $this->_attributeGroupsById[$attributeSetId][$id] = $name;
            $this->_attributeGroupsByName[$attributeSetId][strtolower($name)] = $id;
        }
        return $this;
    }

    public function getAttributeGroupName($attributeSetId, $id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        return isset($this->_attributeGroupsById[$attributeSetId][$id]) ? $this->_attributeGroupsById[$attributeSetId][$id] : false;
    }

    public function getAttributeGroupId($attributeSetId, $name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadAttributeGroups();

        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getAttributeSetId($attributeSetId);
        }
        $name = strtolower($name);
        return isset($this->_attributeGroupsByName[$attributeSetId][$name]) ? $this->_attributeGroupsByName[$attributeSetId][$name] : false;
    }

    public function loadProductTypes()
    {
        if ($this->_productTypesById) {
            return $this;
        }

        $productTypeCollection = Mage::getModel('Mage_Catalog_Model_Product_Type')
            ->getOptionArray();

        $this->_productTypesById = array();
        $this->_productTypesByName = array();
        foreach ($productTypeCollection as $id=>$type) {
            $name = $type;
            $this->_productTypesById[$id] = $name;
            $this->_productTypesByName[strtolower($name)] = $id;
        }
        return $this;
    }

    public function getProductTypeId($name)
    {
        if (is_numeric($name)) {
            return $name;
        }

        $this->loadProductTypes();

        $name = strtolower($name);
        return isset($this->_productTypesByName[$name]) ? $this->_productTypesByName[$name] : false;
    }

    public function getProductTypeName($id)
    {
        if (!is_numeric($id)) {
            return $id;
        }

        $this->loadProductTypes();

        return isset($this->_productTypesById[$id]) ? $this->_productTypesById[$id] : false;
    }

    public function getSourceOptionId($source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value)==0 || $option['value'] == $value) {
                return $option['value'];
            }
        }
        return null;
    }

    /**
     * Load Product attributes
     *
     * @return array
     */
    public function getProductAttributes()
    {
        if ($this->_productAttributes === null) {
            $this->_productAttributes = array_keys($this->getAttributesUsedInProductListing());
        }
        return $this->_productAttributes;
    }

    /**
     * Retrieve Product Collection Attributes from XML config file
     * Used only for install/upgrade
     *
     * @return array
     */
    public function getProductCollectionAttributes() {
        $attributes = Mage::getConfig()
            ->getNode(self::XML_PATH_PRODUCT_COLLECTION_ATTRIBUTES)
            ->asArray();
        return array_keys($attributes);;
    }

    /**
     * Retrieve resource model
     *
     * @return Mage_Catalog_Model_Resource_Config
     */
    protected function _getResource()
    {
        return Mage::getResourceModel('Mage_Catalog_Model_Resource_Config');
    }

    /**
     * Retrieve Attributes used in product listing
     *
     * @return array
     */
    public function getAttributesUsedInProductListing() {
        if ($this->_usedInProductListing === null) {
            $this->_usedInProductListing = array();
            $entityType = Mage_Catalog_Model_Product::ENTITY;
            $attributesData = $this->_getResource()
                ->setStoreId($this->getStoreId())
                ->getAttributesUsedInListing();
            Mage::getSingleton('Mage_Eav_Model_Config')
                ->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedInProductListing[$attributeCode] = Mage::getSingleton('Mage_Eav_Model_Config')
                    ->getAttribute($entityType, $attributeCode);
            }
        }
        return $this->_usedInProductListing;
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @return array
     */
    public function getAttributesUsedForSortBy() {
        if ($this->_usedForSortBy === null) {
            $this->_usedForSortBy = array();
            $entityType     = Mage_Catalog_Model_Product::ENTITY;
            $attributesData = $this->_getResource()
                ->getAttributesUsedForSortBy();
            Mage::getSingleton('Mage_Eav_Model_Config')
                ->importAttributesData($entityType, $attributesData);
            foreach ($attributesData as $attributeData) {
                $attributeCode = $attributeData['attribute_code'];
                $this->_usedForSortBy[$attributeCode] = Mage::getSingleton('Mage_Eav_Model_Config')
                    ->getAttribute($entityType, $attributeCode);
            }
        }
        return $this->_usedForSortBy;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = array(
            'position'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Position')
        );
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }

    /**
     * Retrieve Product List Default Sort By
     *
     * @param mixed $store
     * @return string
     */
    public function getProductListDefaultSortBy($store = null) {
        return Mage::getStoreConfig(self::XML_PATH_LIST_DEFAULT_SORT_BY, $store);
    }
}
