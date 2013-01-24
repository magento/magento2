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

/**
 * Catalog attribute model
 *
 * @method Mage_Catalog_Model_Resource_Attribute _getResource()
 * @method Mage_Catalog_Model_Resource_Attribute getResource()
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getFrontendInputRenderer()
 * @method string setFrontendInputRenderer(string $value)
 * @method int setIsGlobal(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsVisible()
 * @method int setIsVisible(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsSearchable()
 * @method int setIsSearchable(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getSearchWeight()
 * @method int setSearchWeight(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsFilterable()
 * @method int setIsFilterable(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsComparable()
 * @method int setIsComparable(int $value)
 * @method int setIsVisibleOnFront(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsHtmlAllowedOnFront()
 * @method int setIsHtmlAllowedOnFront(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsUsedForPriceRules()
 * @method int setIsUsedForPriceRules(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsFilterableInSearch()
 * @method int setIsFilterableInSearch(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getUsedInProductListing()
 * @method int setUsedInProductListing(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getUsedForSortBy()
 * @method int setUsedForSortBy(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsConfigurable()
 * @method int setIsConfigurable(int $value)
 * @method string setApplyTo(string $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsVisibleInAdvancedSearch()
 * @method int setIsVisibleInAdvancedSearch(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getPosition()
 * @method int setPosition(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsWysiwygEnabled()
 * @method int setIsWysiwygEnabled(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsUsedForPromoRules()
 * @method int setIsUsedForPromoRules(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsUsedForCustomerSegment()
 * @method int setIsUsedForCustomerSegment(int $value)
 * @method Mage_Catalog_Model_Resource_Eav_Attribute getIsUsedForTargetRules()
 * @method int setIsUsedForTargetRules(int $value)
 * @method string getFrontendLabel()
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Eav_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    const SCOPE_STORE                           = 0;
    const SCOPE_GLOBAL                          = 1;
    const SCOPE_WEBSITE                         = 2;

    const MODULE_NAME                           = 'Mage_Catalog';
    const ENTITY                                = 'catalog_eav_attribute';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix                     = 'catalog_entity_attribute';
    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject                     = 'attribute';

    /**
     * Array with labels
     *
     * @var array
     */
    static protected $_labels                   = null;

    protected function _construct()
    {
        $this->_init('Mage_Catalog_Model_Resource_Attribute');
    }

    /**
     * Processing object before save data
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->setData('modulePrefix', self::MODULE_NAME);
        if (isset($this->_origData['is_global'])) {
            if (!isset($this->_data['is_global'])) {
                $this->_data['is_global'] = self::SCOPE_GLOBAL;
            }
            if (($this->_data['is_global'] != $this->_origData['is_global'])
                && $this->_getResource()->isUsedBySuperProducts($this)) {
                Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Scope must not be changed, because the attribute is used in configurable products.'));
            }
        }
        if ($this->getFrontendInput() == 'price') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel('Mage_Catalog_Model_Product_Attribute_Backend_Price');
            }
        }
        if ($this->getFrontendInput() == 'textarea') {
            if ($this->getIsWysiwygEnabled()) {
                $this->setIsHtmlAllowedOnFront(1);
            }
        }
        return parent::_beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        /**
         * Fix saving attribute in admin
         */
        Mage::getSingleton('Mage_Eav_Model_Config')->clear();
        Mage::getSingleton('Mage_Index_Model_Indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return parent::_afterSave();
    }

    /**
     * Register indexing event before delete catalog eav attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _beforeDelete()
    {
        if ($this->_getResource()->isUsedBySuperProducts($this)) {
            Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('This attribute is used in configurable products.'));
        }
        Mage::getSingleton('Mage_Index_Model_Indexer')->logEvent(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
        );
        return parent::_beforeDelete();
    }

    /**
     * Init indexing process after catalog eav attribute delete commit
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();
        Mage::getSingleton('Mage_Index_Model_Indexer')->indexEvents(
            self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
        );
        return $this;
    }

    /**
     * Return is attribute global
     *
     * @return integer
     */
    public function getIsGlobal()
    {
        return $this->_getData('is_global');
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    /**
     * Retrieve attribute is website scope website
     *
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     *
     * @return bool
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    /**
     * Retrieve store id
     *
     * @return int
     */
    public function getStoreId()
    {
        $dataObject = $this->getDataObject();
        if ($dataObject) {
            return $dataObject->getStoreId();
        }
        return $this->getData('store_id');
    }

    /**
     * Retrieve apply to products array
     * Return empty array if applied to all products
     *
     * @return array
     */
    public function getApplyTo()
    {
        if ($this->getData('apply_to')) {
            if (is_array($this->getData('apply_to'))) {
                return $this->getData('apply_to');
            }
            return explode(',', $this->getData('apply_to'));
        } else {
            return array();
        }
    }

    /**
     * Retrieve source model
     *
     * @return Mage_Eav_Model_Entity_Attribute_Source_Abstract
     */
    public function getSourceModel()
    {
        $model = $this->getData('source_model');
        if (empty($model)) {
            if ($this->getBackendType() == 'int' && $this->getFrontendInput() == 'select') {
                return $this->_getDefaultSourceModel();
            }
        }
        return $model;
    }

    /**
     * Whether allowed for rule condition
     *
     * @return bool
     */
    public function isAllowedForRuleCondition()
    {
        $allowedInputTypes = array(
            'boolean',
            'date',
            'datetime',
            'multiselect',
            'price',
            'select',
            'text',
            'textarea',
            'weight',
        );
        return $this->getIsVisible() && in_array($this->getFrontendInput(), $allowedInputTypes);
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function _getDefaultSourceModel()
    {
        return 'Mage_Eav_Model_Entity_Attribute_Source_Table';
    }

    /**
     * Check is an attribute used in EAV index
     *
     * @return bool
     */
    public function isIndexable()
    {
        // exclude price attribute
        if ($this->getAttributeCode() == 'price') {
            return false;
        }

        if (!$this->getIsFilterableInSearch() && !$this->getIsVisibleInAdvancedSearch() && !$this->getIsFilterable()) {
            return false;
        }

        $backendType    = $this->getBackendType();
        $frontendInput  = $this->getFrontendInput();

        if ($backendType == 'int' && $frontendInput == 'select') {
            return true;
        } else if ($backendType == 'varchar' && $frontendInput == 'multiselect') {
            return true;
        } else if ($backendType == 'decimal') {
            return true;
        }

        return false;
    }

    /**
     * Retrieve index type for indexable attribute
     *
     * @return string|false
     */
    public function getIndexType()
    {
        if (!$this->isIndexable()) {
            return false;
        }
        if ($this->getBackendType() == 'decimal') {
            return 'decimal';
        }

        return 'source';
    }
}
