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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{
    /**
     * Catalog Product Flat is enabled cache per store
     *
     * @var array
     */
    protected $_flatEnabled                  = array();

    /**
     * Product websites table name
     *
     * @var string
     */
    protected $_productWebsiteTable;

    /**
     * Product categories table name
     *
     * @var string
     */
    protected $_productCategoryTable;

    /**
     * Is add URL rewrites to collection flag
     *
     * @var bool
     */
    protected $_addUrlRewrite                = false;

    /**
     * Add URL rewrite for category
     *
     * @var int
     */
    protected $_urlRewriteCategory           = '';

    /**
     * Is add final price to product collection flag
     *
     * @var unknown_type
     */
    protected $_addFinalPrice                = false;

    /**
     * Cache for all ids
     *
     * @var array
     */
    protected $_allIdsCache                  = null;

    /**
     * Is add tax percents to product collection flag
     *
     * @var bool
     */
    protected $_addTaxPercents               = false;

    /**
     * Product limitation filters
     * Allowed filters
     *  store_id                int;
     *  category_id             int;
     *  category_is_anchor      int;
     *  visibility              array|int;
     *  website_ids             array|int;
     *  store_table             string;
     *  use_price_index         bool;   join price index table flag
     *  customer_group_id       int;    required for price; customer group limitation for price
     *  website_id              int;    required for price; website limitation for price
     *
     * @var array
     */
    protected $_productLimitationFilters     = array();

    /**
     * Category product count select
     *
     * @var Zend_Db_Select
     */
    protected $_productCountSelect           = null;

    /**
     * Enter description here ...
     *
     * @var bool
     */
    protected $_isWebsiteFilter              = false;

    /**
     * Additional field filters, applied in _productLimitationJoinPrice()
     *
     * @var array
     */
    protected $_priceDataFieldFilters = array();

    /**
     * Map of price fields
     *
     * @var array
     */
    protected $_map = array('fields' => array(
        'price'         => 'price_index.price',
        'final_price'   => 'price_index.final_price',
        'min_price'     => 'price_index.min_price',
        'max_price'     => 'price_index.max_price',
        'tier_price'    => 'price_index.tier_price',
        'special_price' => 'price_index.special_price',
    ));

    /**
     * Retrieve Catalog Product Flat Helper object
     *
     * @return Mage_Catalog_Helper_Product_Flat
     */
    public function getFlatHelper()
    {
        return Mage::helper('Mage_Catalog_Helper_Product_Flat');
    }

    /**
     * Retrieve is flat enabled flag
     * Return alvays false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        if (!isset($this->_flatEnabled[$this->getStoreId()])) {
            $this->_flatEnabled[$this->getStoreId()] = $this->getFlatHelper()
                ->isEnabled($this->getStoreId());
        }
        return $this->_flatEnabled[$this->getStoreId()];
    }

    /**
     * Initialize resources
     *
     */
    protected function _construct()
    {
        if ($this->isEnabledFlat()) {
            $this->_init('Mage_Catalog_Model_Product', 'Mage_Catalog_Model_Resource_Product_Flat');
        }
        else {
            $this->_init('Mage_Catalog_Model_Product', 'Mage_Catalog_Model_Resource_Product');
        }
        $this->_initTables();
    }

    /**
     * Define product website and category product tables
     *
     */
    protected function _initTables()
    {
        $this->_productWebsiteTable = $this->getResource()->getTable('catalog_product_website');
        $this->_productCategoryTable= $this->getResource()->getTable('catalog_category_product');
    }

    /**
     * Standard resource collection initalization
     *
     * @param string $model
     * @param unknown_type $entityModel
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _init($model, $entityModel)
    {
        if ($this->isEnabledFlat()) {
            $entityModel = 'catalog/product_flat';
        }

        return parent::_init($model, $entityModel);
    }

    /**
     * Prepare static entity fields
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _prepareStaticFields()
    {
        if ($this->isEnabledFlat()) {
            return $this;
        }
        return parent::_prepareStaticFields();
    }

    /**
     * Retrieve collection empty item
     * Redeclared for specifying id field name without getting resource model inside model
     *
     * @return Varien_Object
     */
    public function getNewEmptyItem()
    {
        $object = parent::getNewEmptyItem();
        if ($this->isEnabledFlat()) {
            $object->setIdFieldName($this->getEntity()->getIdFieldName());
        }
        return $object;
    }

    /**
     * Set entity to use for attributes
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function setEntity($entity)
    {
        if ($this->isEnabledFlat() && ($entity instanceof Mage_Core_Model_Resource_Db_Abstract)) {
            $this->_entity = $entity;
            return $this;
        }
        return parent::setEntity($entity);
    }

    /**
     * Set Store scope for collection
     *
     * @param mixed $store
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function setStore($store)
    {
        parent::setStore($store);
        if ($this->isEnabledFlat()) {
            $this->getEntity()->setStoreId($this->getStoreId());
        }
        return $this;
    }

    /**
     * Initialize collection select
     * Redeclared for remove entity_type_id condition
     * in catalog_product_entity we store just products
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _initSelect()
    {
        if ($this->isEnabledFlat()) {
            $this->getSelect()
                ->from(array('e' => $this->getEntity()->getFlatTableName()), null)
                ->columns(array('status' => new Zend_Db_Expr(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)));
            $this->addAttributeToSelect(array('entity_id', 'type_id', 'attribute_set_id'));
            if ($this->getFlatHelper()->isAddChildData()) {
                $this->getSelect()
                    ->where('e.is_child=?', 0);
                $this->addAttributeToSelect(array('child_id', 'is_child'));
            }
        } else {
            $this->getSelect()->from(array('e'=>$this->getEntity()->getEntityTable()));
        }
        return $this;
    }

    /**
     * Load attributes into loaded entities
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if ($this->isEnabledFlat()) {
            return $this;
        }
        return parent::_loadAttributes($printQuery, $logQuery);
    }

    /**
     * Add attribute to entities in collection
     * If $attribute=='*' select all attributes
     *
     * @param array|string|integer|Mage_Core_Model_Config_Element $attribute
     * @param false|string $joinType
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        if ($this->isEnabledFlat()) {
            if (!is_array($attribute)) {
                $attribute = array($attribute);
            }
            foreach ($attribute as $attributeCode) {
                if ($attributeCode == '*') {
                    foreach ($this->getEntity()->getAllTableColumns() as $column) {
                        $this->getSelect()->columns('e.' . $column);
                        $this->_selectAttributes[$column] = $column;
                        $this->_staticFields[$column]     = $column;
                    }
                } else {
                    $columns = $this->getEntity()->getAttributeForSelect($attributeCode);
                    if ($columns) {
                        foreach ($columns as $alias => $column) {
                            $this->getSelect()->columns(array($alias => 'e.' . $column));
                            $this->_selectAttributes[$column] = $column;
                            $this->_staticFields[$column]     = $column;
                        }
                    }
                }
            }
            return $this;
        }
        return parent::addAttributeToSelect($attribute, $joinType);
    }

    /**
     * Add tax class id attribute to select and join price rules data if needed
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _beforeLoad()
    {
        Mage::dispatchEvent('catalog_product_collection_load_before', array('collection' => $this));

        return parent::_beforeLoad();
    }

    /**
     * Processing collection items after loading
     * Adding url rewrites, minimal prices, final prices, tax percents
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _afterLoad()
    {
        if ($this->_addUrlRewrite) {
           $this->_addUrlRewrite($this->_urlRewriteCategory);
        }

        $this->_prepareUrlDataObject();

        if (count($this) > 0) {
            Mage::dispatchEvent('catalog_product_collection_load_after', array('collection' => $this));
        }

        foreach ($this as $product) {
            if ($product->isRecurring() && $profile = $product->getRecurringProfile()) {
                $product->setRecurringProfile(unserialize($profile));
            }
        }

        return $this;
    }

    /**
     * Prepare Url Data object
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _prepareUrlDataObject()
    {
        $objects = array();
        /** @var $item Mage_Catalog_Model_Product */
        foreach ($this->_items as $item) {
            if ($this->getFlag('do_not_use_category_id')) {
                $item->setDoNotUseCategoryId(true);
            }
            if (!$item->isVisibleInSiteVisibility() && $item->getItemStoreId()) {
                $objects[$item->getEntityId()] = $item->getItemStoreId();
            }
        }

        if ($objects && $this->hasFlag('url_data_object')) {
            $objects = Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Url')
                ->getRewriteByProductStore($objects);
            foreach ($this->_items as $item) {
                if (isset($objects[$item->getEntityId()])) {
                    $object = new Varien_Object($objects[$item->getEntityId()]);
                    $item->setUrlDataObject($object);
                }
            }
        }

        return $this;
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $productId
     * @param boolean $exclude
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addIdFilter($productId, $exclude = false)
    {
        if (empty($productId)) {
            $this->_setIsLoaded(true);
            return $this;
        }
        if (is_array($productId)) {
            if (!empty($productId)) {
                if ($exclude) {
                    $condition = array('nin' => $productId);
                } else {
                    $condition = array('in' => $productId);
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = array('neq' => $productId);
            } else {
                $condition = $productId;
            }
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    /**
     * Adding product website names to result collection
     * Add for each product websites information
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addWebsiteNamesToResult()
    {
        $productWebsites = array();
        foreach ($this as $product) {
            $productWebsites[$product->getId()] = array();
        }

        if (!empty($productWebsites)) {
            $select = $this->getConnection()->select()
                ->from(array('product_website' => $this->_productWebsiteTable))
                ->join(
                    array('website' => $this->getResource()->getTable('core_website')),
                    'website.website_id = product_website.website_id',
                    array('name'))
                ->where('product_website.product_id IN (?)', array_keys($productWebsites))
                ->where('website.website_id > ?', 0);

            $data = $this->getConnection()->fetchAll($select);
            foreach ($data as $row) {
                $productWebsites[$row['product_id']][] = $row['website_id'];
            }
        }

        foreach ($this as $product) {
            if (isset($productWebsites[$product->getId()])) {
                $product->setData('websites', $productWebsites[$product->getId()]);
            }
        }
        return $this;
    }

    /**
     * Add store availability filter. Include availability product
     * for store website
     *
     * @param mixed $store
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addStoreFilter($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }
        $store = Mage::app()->getStore($store);

        if (!$store->isAdmin()) {
            $this->setStoreId($store);
            $this->_productLimitationFilters['store_id'] = $store->getId();
            $this->_applyProductLimitations();
        }

        return $this;
    }

    /**
     * Add website filter to collection
     *
     * @param unknown_type $websites
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addWebsiteFilter($websites = null)
    {
        if (!is_array($websites)) {
            $websites = array(Mage::app()->getWebsite($websites)->getId());
        }

        $this->_productLimitationFilters['website_ids'] = $websites;
        $this->_applyProductLimitations();

        return $this;
    }

    /**
     * Get filters applied to collection
     *
     * @return array
     */
    public function getLimitationFilters()
    {
        return $this->_productLimitationFilters;
    }

    /**
     * Specify category filter for product collection
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addCategoryFilter(Mage_Catalog_Model_Category $category)
    {
        $this->_productLimitationFilters['category_id'] = $category->getId();
        if ($category->getIsAnchor()) {
            unset($this->_productLimitationFilters['category_is_anchor']);
        } else {
            $this->_productLimitationFilters['category_is_anchor'] = 1;
        }

        if ($this->getStoreId() == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $this->_applyZeroStoreProductLimitations();
        } else {
            $this->_applyProductLimitations();
        }

        return $this;
    }

    /**
     * Join minimal price attribute to result
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function joinMinimalPrice()
    {
        $this->addAttributeToSelect('price')
             ->addAttributeToSelect('minimal_price');
        return $this;
    }

    /**
     * Retrieve max value by attribute
     *
     * @param string $attribute
     * @return mixed
     */
    public function getMaxAttributeValue($attribute)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_max_value';
        $fieldAlias    = 'max_' . $attributeCode;
        $condition  = 'e.entity_id = ' . $tableAlias . '.entity_id
            AND '.$this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->join(
                array($tableAlias => $attribute->getBackend()->getTable()),
                $condition,
                array($fieldAlias => new Zend_Db_Expr('MAX('.$tableAlias.'.value)'))
            )
            ->group('e.entity_type_id');

        $data = $this->getConnection()->fetchRow($select);
        if (isset($data[$fieldAlias])) {
            return $data[$fieldAlias];
        }

        return null;
    }

    /**
     * Retrieve ranging product count for arrtibute range
     *
     * @param string $attribute
     * @param int $range
     * @return array
     */
    public function getAttributeValueCountByRange($attribute, $range)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_range_count_value';

        $condition  = 'e.entity_id = ' . $tableAlias . '.entity_id
            AND ' . $this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->reset(Zend_Db_Select::GROUP);
        $select->join(
                array($tableAlias => $attribute->getBackend()->getTable()),
                $condition,
                array(
                    'count_' . $attributeCode => new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)'),
                    'range_' . $attributeCode => new Zend_Db_Expr(
                        'CEIL((' . $tableAlias . '.value+0.01)/' . $range . ')')
                 )
            )
            ->group('range_' . $attributeCode);

        $data   = $this->getConnection()->fetchAll($select);
        $res    = array();

        foreach ($data as $row) {
            $res[$row['range_' . $attributeCode]] = $row['count_' . $attributeCode];
        }
        return $res;
    }

    /**
     * Retrieve product count by some value of attribute
     *
     * @param string $attribute
     * @return array($value=>$count)
     */
    public function getAttributeValueCount($attribute)
    {
        $select        = clone $this->getSelect();
        $attribute     = $this->getEntity()->getAttribute($attribute);
        $attributeCode = $attribute->getAttributeCode();
        $tableAlias    = $attributeCode . '_value_count';

        $select->reset(Zend_Db_Select::GROUP);
        $condition  = 'e.entity_id=' . $tableAlias . '.entity_id
            AND '.$this->_getConditionSql($tableAlias . '.attribute_id', $attribute->getId());

        $select->join(
                array($tableAlias => $attribute->getBackend()->getTable()),
                $condition,
                array(
                    'count_' . $attributeCode => new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)'),
                    'value_' . $attributeCode => new Zend_Db_Expr($tableAlias . '.value')
                 )
            )
            ->group('value_' . $attributeCode);

        $data   = $this->getConnection()->fetchAll($select);
        $res    = array();

        foreach ($data as $row) {
            $res[$row['value_' . $attributeCode]] = $row['count_' . $attributeCode];
        }
        return $res;
    }

    /**
     * Return all attribute values as array in form:
     * array(
     *   [entity_id_1] => array(
     *          [store_id_1] => store_value_1,
     *          [store_id_2] => store_value_2,
     *          ...
     *          [store_id_n] => store_value_n
     *   ),
     *   ...
     * )
     *
     * @param string $attribute attribute code
     * @return array
     */
    public function getAllAttributeValues($attribute)
    {
        /** @var $select Varien_Db_Select */
        $select    = clone $this->getSelect();
        $attribute = $this->getEntity()->getAttribute($attribute);

        $select->reset()
            ->from($attribute->getBackend()->getTable(), array('entity_id', 'store_id', 'value'))
            ->where('attribute_id = ?', (int)$attribute->getId());

        $data = $this->getConnection()->fetchAll($select);
        $res  = array();

        foreach ($data as $row) {
            $res[$row['entity_id']][$row['store_id']] = $row['value'];
        }

        return $res;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = $this->_getClearSelect()
            ->columns('COUNT(DISTINCT e.entity_id)')
            ->resetJoinLeft();
        return $countSelect;
    }

    /**
     * Retreive clear select
     *
     * @return Varien_Db_Select
     */
    protected function _getClearSelect()
    {
        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);

        return $select;
    }
    /**
     * Retrive all ids for collection
     *
     * @param unknown_type $limit
     * @param unknown_type $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retreive product count select for categories
     *
     * @return Varien_Db_Select
     */
    public function getProductCountSelect()
    {
        if ($this->_productCountSelect === null) {
            $this->_productCountSelect = clone $this->getSelect();
            $this->_productCountSelect->reset(Zend_Db_Select::COLUMNS)
                ->reset(Zend_Db_Select::GROUP)
                ->reset(Zend_Db_Select::ORDER)
                ->distinct(false)
                ->join(array('count_table' => $this->getTable('catalog_category_product_index')),
                    'count_table.product_id = e.entity_id',
                    array(
                        'count_table.category_id',
                        'product_count' => new Zend_Db_Expr('COUNT(DISTINCT count_table.product_id)')
                    )
                )
                ->where('count_table.store_id = ?', $this->getStoreId())
                ->group('count_table.category_id');
        }

        return $this->_productCountSelect;
    }

    /**
     * Destruct product count select
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function unsProductCountSelect()
    {
        $this->_productCountSelect = null;
        return $this;
    }

    /**
     * Adding product count to categories collection
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $categoryCollection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addCountToCategories($categoryCollection)
    {
        $isAnchor    = array();
        $isNotAnchor = array();
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[]    = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = array();
        if ($isAnchor || $isNotAnchor) {
            $select = $this->getProductCountSelect();

            Mage::dispatchEvent(
                'catalog_product_collection_before_add_count_to_categories',
                array('collection' => $this)
            );

            if ($isAnchor) {
                $anchorStmt = clone $select;
                $anchorStmt->limit(); //reset limits
                $anchorStmt->where('count_table.category_id IN (?)', $isAnchor);
                $productCounts += $this->getConnection()->fetchPairs($anchorStmt);
                $anchorStmt = null;
            }
            if ($isNotAnchor) {
                $notAnchorStmt = clone $select;
                $notAnchorStmt->limit(); //reset limits
                $notAnchorStmt->where('count_table.category_id IN (?)', $isNotAnchor);
                $notAnchorStmt->where('count_table.is_parent = 1');
                $productCounts += $this->getConnection()->fetchPairs($notAnchorStmt);
                $notAnchorStmt = null;
            }
            $select = null;
            $this->unsProductCountSelect();
        }

        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            $category->setProductCount($_count);
        }

        return $this;
    }

    /**
     * Retrieve unique attribute set ids in collection
     *
     * @return array
     */
    public function getSetIds()
    {
        $select = clone $this->getSelect();
        /** @var $select Varien_Db_Select */
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->distinct(true);
        $select->columns('attribute_set_id');
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Return array of unique product type ids in collection
     *
     * @return array
     */
    public function getProductTypeIds()
    {
        $select = clone $this->getSelect();
        /** @var $select Varien_Db_Select */
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->distinct(true);
        $select->columns('type_id');
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Joins url rewrite rules to collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function joinUrlRewrite()
    {
        $this->joinTable(
            'core_url_rewrite',
            'entity_id=entity_id',
            array('request_path'),
            '{{table}}.type = ' . Mage_Core_Model_Url_Rewrite::TYPE_PRODUCT,
            'left'
        );

        return $this;
    }

    /**
     * Add URL rewrites data to product
     * If collection loadded - run processing else set flag
     *
     * @param int $categoryId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addUrlRewrite($categoryId = '')
    {
        $this->_addUrlRewrite = true;
        if (Mage::getStoreConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY, $this->getStoreId())) {
            $this->_urlRewriteCategory = $categoryId;
        } else {
            $this->_urlRewriteCategory = 0;
        }

        if ($this->isLoaded()) {
            $this->_addUrlRewrite();
        }

        return $this;
    }

    /**
     * Add URL rewrites to collection
     *
     */
    protected function _addUrlRewrite()
    {
        $urlRewrites = null;
        if ($this->_cacheConf) {
            if (!($urlRewrites = Mage::app()->loadCache($this->_cacheConf['prefix'] . 'urlrewrite'))) {
                $urlRewrites = null;
            } else {
                $urlRewrites = unserialize($urlRewrites);
            }
        }

        if (!$urlRewrites) {
            $productIds = array();
            foreach($this->getItems() as $item) {
                $productIds[] = $item->getEntityId();
            }
            if (!count($productIds)) {
                return;
            }

            $select = $this->getConnection()->select()
                ->from($this->getTable('core_url_rewrite'), array('product_id', 'request_path'))
                ->where('store_id = ?', Mage::app()->getStore()->getId())
                ->where('is_system = ?', 1)
                ->where('category_id = ? OR category_id IS NULL', $this->_urlRewriteCategory)
                ->where('product_id IN(?)', $productIds)
                ->order('category_id ' . self::SORT_ORDER_DESC); // more priority is data with category id
            $urlRewrites = array();

            foreach ($this->getConnection()->fetchAll($select) as $row) {
                if (!isset($urlRewrites[$row['product_id']])) {
                    $urlRewrites[$row['product_id']] = $row['request_path'];
                }
            }

            if ($this->_cacheConf) {
                Mage::app()->saveCache(
                    serialize($urlRewrites),
                    $this->_cacheConf['prefix'] . 'urlrewrite',
                    array_merge($this->_cacheConf['tags'], array(Mage_Catalog_Model_Product_Url::CACHE_TAG)),
                    $this->_cacheLifetime
                );
            }
        }

        foreach($this->getItems() as $item) {
            if (isset($urlRewrites[$item->getEntityId()])) {
                $item->setData('request_path', $urlRewrites[$item->getEntityId()]);
            } else {
                $item->setData('request_path', false);
            }
        }
    }

    /**
     * Add minimal price data to result
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addMinimalPrice()
    {
        return $this->addPriceData();
    }

    /**
     * Add price data for calculate final price
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addFinalPrice()
    {
        return $this->addPriceData();
    }

    /**
     * Join prices from price rules to products collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _joinPriceRules()
    {
        if ($this->isEnabledFlat()) {
            $customerGroup = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();
            $priceColumn   = 'e.display_price_group_' . $customerGroup;
            $this->getSelect()->columns(array('_rule_price' => $priceColumn));

            return $this;
        }
        if (!Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_CatalogRule')) {
            return $this;
        }
        $wId = Mage::app()->getWebsite()->getId();
        $gId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();

        $storeDate = Mage::app()->getLocale()->storeTimeStamp($this->getStoreId());
        $conditions  = 'price_rule.product_id = e.entity_id AND ';
        $conditions .= "price_rule.rule_date = '".$this->getResource()->formatDate($storeDate, false)."' AND ";
        $conditions .= $this->getConnection()->quoteInto('price_rule.website_id = ? AND', $wId);
        $conditions .= $this->getConnection()->quoteInto('price_rule.customer_group_id = ?', $gId);

        $this->getSelect()->joinLeft(
            array('price_rule' => $this->getTable('catalogrule_product_price')),
            $conditions,
            array('rule_price' => 'rule_price')
        );
        return $this;
    }

    /**
     * Retreive all ids
     *
     * @param boolean $resetCache
     * @return array
     */
    public function getAllIdsCache($resetCache = false)
    {
        $ids = null;
        if (!$resetCache) {
            $ids = $this->_allIdsCache;
        }

        if (is_null($ids)) {
            $ids = $this->getAllIds();
            $this->setAllIdsCache($ids);
        }

        return $ids;
    }

    /**
     * Set all ids
     *
     * @param array $value
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function setAllIdsCache($value)
    {
        $this->_allIdsCache = $value;
        return $this;
    }

    /**
     * Add Price Data to result
     *
     * @param int $customerGroupId
     * @param int $websiteId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addPriceData($customerGroupId = null, $websiteId = null)
    {
        $this->_productLimitationFilters['use_price_index'] = true;

        if (!isset($this->_productLimitationFilters['customer_group_id']) && is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();
        }
        if (!isset($this->_productLimitationFilters['website_id']) && is_null($websiteId)) {
            $websiteId       = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        }

        if (!is_null($customerGroupId)) {
            $this->_productLimitationFilters['customer_group_id'] = $customerGroupId;
        }
        if (!is_null($websiteId)) {
            $this->_productLimitationFilters['website_id'] = $websiteId;
        }

        $this->_applyProductLimitations();

        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|string $attribute
     * @param array $condition
     * @param string $joinType
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        if ($this->isEnabledFlat()) {
            if ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
                $attribute = $attribute->getAttributeCode();
            }

            if (is_array($attribute)) {
                $sqlArr = array();
                foreach ($attribute as $condition) {
                    $sqlArr[] = $this->_getAttributeConditionSql($condition['attribute'], $condition, $joinType);
                }
                $conditionSql = '('.join(') OR (', $sqlArr).')';
                $this->getSelect()->where($conditionSql);
                return $this;
            }

            if (!isset($this->_selectAttributes[$attribute])) {
                $this->addAttributeToSelect($attribute);
            }

            if (isset($this->_selectAttributes[$attribute])) {
                $this->getSelect()->where($this->_getConditionSql('e.' . $attribute, $condition));
            }

            return $this;
        }

        $this->_allIdsCache = null;

        if (is_string($attribute) && $attribute == 'is_saleable') {
            $columns = $this->getSelect()->getPart(Zend_Db_Select::COLUMNS);
            foreach ($columns as $columnEntry) {
                list($correlationName, $column, $alias) = $columnEntry;
                if ($alias == 'is_saleable') {
                    if ($column instanceof Zend_Db_Expr) {
                        $field = $column;
                    } else {
                        $adapter = $this->getSelect()->getAdapter();
                        if (empty($correlationName)) {
                            $field = $adapter->quoteColumnAs($column, $alias, true);
                        } else {
                            $field = $adapter->quoteColumnAs(array($correlationName, $column), $alias, true);
                        }
                    }
                    $this->getSelect()->where("{$field} = ?", $condition);
                    break;
                }
            }

            return $this;
        } else {
            return parent::addAttributeToFilter($attribute, $condition, $joinType);
        }
    }

    /**
     * Add requere tax percent flag for product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addTaxPercents()
    {
        $this->_addTaxPercents = true;
        return $this;
    }

    /**
     * Get require tax percent flag value
     *
     * @return bool
     */
    public function requireTaxPercent()
    {
        return $this->_addTaxPercents;
    }


    /**
     * Adding product custom options to result collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addOptionsToResult()
    {
        $productIds = array();
        foreach ($this as $product) {
            $productIds[] = $product->getId();
        }
        if (!empty($productIds)) {
            $options = Mage::getModel('Mage_Catalog_Model_Product_Option')
                ->getCollection()
                ->addTitleToResult(Mage::app()->getStore()->getId())
                ->addPriceToResult(Mage::app()->getStore()->getId())
                ->addProductToFilter($productIds)
                ->addValuesToResult();

            foreach ($options as $option) {
                if($this->getItemById($option->getProductId())) {
                    $this->getItemById($option->getProductId())->addOption($option);
                }
            }
        }

        return $this;
    }

    /**
     * Filter products with required options
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addFilterByRequiredOptions()
    {
        $this->addAttributeToFilter('required_options', array(array('neq' => '1'), array('null' => true)), 'left');
        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function setVisibility($visibility)
    {
        $this->_productLimitationFilters['visibility'] = $visibility;
        $this->_applyProductLimitations();

        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if ($attribute == 'position') {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
                return $this;
            }
            if ($this->isEnabledFlat()) {
                $this->getSelect()->order("cat_index_position {$dir}");
            }
            // optimize if using cat index
            $filters = $this->_productLimitationFilters;
            if (isset($filters['category_id']) || isset($filters['visibility'])) {
                $this->getSelect()->order('cat_index.position ' . $dir);
            } else {
                $this->getSelect()->order('e.entity_id ' . $dir);
            }

            return $this;
        } elseif($attribute == 'is_saleable'){
            $this->getSelect()->order("is_saleable " . $dir);
            return $this;
        }

        $storeId = $this->getStoreId();
        if ($attribute == 'price' && $storeId != 0) {
            $this->addPriceData();
            $this->getSelect()->order("price_index.min_price {$dir}");

            return $this;
        }

        if ($this->isEnabledFlat()) {
            $column = $this->getEntity()->getAttributeSortColumn($attribute);

            if ($column) {
                $this->getSelect()->order("e.{$column} {$dir}");
            }
            else if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
            }

            return $this;
        } else {
            $attrInstance = $this->getEntity()->getAttribute($attribute);
            if ($attrInstance && $attrInstance->usesSource()) {
                $attrInstance->getSource()
                    ->addValueSortToCollection($this, $dir);
                return $this;
            }
        }

        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * Prepare limitation filters
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _prepareProductLimitationFilters()
    {
        if (isset($this->_productLimitationFilters['visibility'])
            && !isset($this->_productLimitationFilters['store_id'])
        ) {
            $this->_productLimitationFilters['store_id'] = $this->getStoreId();
        }
        if (isset($this->_productLimitationFilters['category_id'])
            && !isset($this->_productLimitationFilters['store_id'])
        ) {
            $this->_productLimitationFilters['store_id'] = $this->getStoreId();
        }
        if (isset($this->_productLimitationFilters['store_id'])
            && isset($this->_productLimitationFilters['visibility'])
            && !isset($this->_productLimitationFilters['category_id'])
        ) {
            $this->_productLimitationFilters['category_id'] = Mage::app()
                ->getStore($this->_productLimitationFilters['store_id'])
                ->getRootCategoryId();
        }

        return $this;
    }

    /**
     * Join website product limitation
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _productLimitationJoinWebsite()
    {
        $joinWebsite = false;
        $filters     = $this->_productLimitationFilters;
        $conditions  = array('product_website.product_id = e.entity_id');

        if (isset($filters['website_ids'])) {
            $joinWebsite = true;
            if (count($filters['website_ids']) > 1) {
                $this->getSelect()->distinct(true);
            }
            $conditions[] = $this->getConnection()
                ->quoteInto('product_website.website_id IN(?)', $filters['website_ids']);
        } elseif (isset($filters['store_id'])
            && (!isset($filters['visibility']) && !isset($filters['category_id']))
            && !$this->isEnabledFlat()
        ) {
            $joinWebsite = true;
            $websiteId = Mage::app()->getStore($filters['store_id'])->getWebsiteId();
            $conditions[] = $this->getConnection()
                ->quoteInto('product_website.website_id = ?', $websiteId);
        }

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['product_website'])) {
            if (!$joinWebsite) {
                unset($fromPart['product_website']);
            } else {
                $fromPart['product_website']['joinCondition'] = join(' AND ', $conditions);
            }
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        } elseif ($joinWebsite) {
            $this->getSelect()->join(
                array('product_website' => $this->getTable('catalog_product_website')),
                join(' AND ', $conditions),
                array()
            );
        }

        return $this;
    }

    /**
     * Join additional (alternative) store visibility filter
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _productLimitationJoinStore()
    {
        $filters = $this->_productLimitationFilters;
        if (!isset($filters['store_table'])) {
            return $this;
        }

        $hasColumn = false;
        foreach ($this->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $columnEntry) {
            list(,,$alias) = $columnEntry;
            if ($alias == 'visibility') {
                $hasColumn = true;
            }
        }
        if (!$hasColumn) {
            $this->getSelect()->columns('visibility', 'cat_index');
        }

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['store_index'])) {
            $this->getSelect()->joinLeft(
                array('store_index' => $this->getTable('core_store')),
                'store_index.store_id = ' . $filters['store_table'] . '.store_id',
                array()
            );
        }
        if (!isset($fromPart['store_group_index'])) {
            $this->getSelect()->joinLeft(
                array('store_group_index' => $this->getTable('core_store_group')),
                'store_index.group_id = store_group_index.group_id',
                array()
            );
        }
        if (!isset($fromPart['store_cat_index'])) {
            $this->getSelect()->joinLeft(
                array('store_cat_index' => $this->getTable('catalog_category_product_index')),
                join(' AND ', array(
                    'store_cat_index.product_id = e.entity_id',
                    'store_cat_index.store_id = ' . $filters['store_table'] . '.store_id',
                    'store_cat_index.category_id=store_group_index.root_category_id'
                )),
                array('store_visibility' => 'visibility')
            );
        }
        // Avoid column duplication problems
        Mage::getResourceHelper('Mage_Core')->prepareColumnsList($this->getSelect());

        $whereCond = join(' OR ', array(
            $this->getConnection()->quoteInto('cat_index.visibility IN(?)', $filters['visibility']),
            $this->getConnection()->quoteInto('store_cat_index.visibility IN(?)', $filters['visibility'])
        ));

        $wherePart = $this->getSelect()->getPart(Zend_Db_Select::WHERE);
        $hasCond   = false;
        foreach ($wherePart as $cond) {
            if ($cond == '(' . $whereCond . ')') {
                $hasCond = true;
            }
        }

        if (!$hasCond) {
            $this->getSelect()->where($whereCond);
        }

        return $this;
    }

    /**
     * Join Product Price Table
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _productLimitationJoinPrice()
    {
        return $this->_productLimitationPrice();
    }

    /**
     * Join Product Price Table with left-join possibility
     *
     * @see Mage_Catalog_Model_Resource_Product_Collection::_productLimitationJoinPrice()
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _productLimitationPrice($joinLeft = false)
    {
        $filters = $this->_productLimitationFilters;
        if (empty($filters['use_price_index'])) {
            return $this;
        }

        $helper     = Mage::getResourceHelper('Mage_Core');
        $connection = $this->getConnection();
        $select     = $this->getSelect();
        $joinCond   = join(' AND ', array(
            'price_index.entity_id = e.entity_id',
            $connection->quoteInto('price_index.website_id = ?', $filters['website_id']),
            $connection->quoteInto('price_index.customer_group_id = ?', $filters['customer_group_id'])
        ));

        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['price_index'])) {
            $least       = $connection->getLeastSql(array('price_index.min_price', 'price_index.tier_price'));
            $minimalExpr = $connection->getCheckSql('price_index.tier_price IS NOT NULL',
                $least, 'price_index.min_price');
            $colls       = array('price', 'tax_class_id', 'final_price',
                'minimal_price' => $minimalExpr , 'min_price', 'max_price', 'tier_price');
            $tableName = array('price_index' => $this->getTable('catalog_product_index_price'));
            if ($joinLeft) {
                $select->joinLeft($tableName, $joinCond, $colls);
            } else {
                $select->join($tableName, $joinCond, $colls);
            }
            // Set additional field filters
            foreach ($this->_priceDataFieldFilters as $filterData) {
                $select->where(call_user_func_array('sprintf', $filterData));
            }
        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $select->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        //Clean duplicated fields
        $helper->prepareColumnsList($select);


        return $this;
    }

    /**
     * Apply front-end price limitation filters to the collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function applyFrontendPriceLimitations()
    {
        $this->_productLimitationFilters['use_price_index'] = true;
        if (!isset($this->_productLimitationFilters['customer_group_id'])) {
            $customerGroupId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerGroupId();
            $this->_productLimitationFilters['customer_group_id'] = $customerGroupId;
        }
        if (!isset($this->_productLimitationFilters['website_id'])) {
            $websiteId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
            $this->_productLimitationFilters['website_id'] = $websiteId;
        }
        $this->_applyProductLimitations();
        return $this;
    }

    /**
     * Apply limitation filters to collection
     * Method allows using one time category product index table (or product website table)
     * for different combinations of store_id/category_id/visibility filter states
     * Method supports multiple changes in one collection object for this parameters
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _applyProductLimitations()
    {
        $this->_prepareProductLimitationFilters();
        $this->_productLimitationJoinWebsite();
        $this->_productLimitationJoinPrice();
        $filters = $this->_productLimitationFilters;

        if (!isset($filters['category_id']) && !isset($filters['visibility'])) {
            return $this;
        }

        $conditions = array(
            'cat_index.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index.store_id=?', $filters['store_id'])
        );
        if (isset($filters['visibility']) && !isset($filters['store_table'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.visibility IN(?)', $filters['visibility']);
        }
        $conditions[] = $this->getConnection()
            ->quoteInto('cat_index.category_id=?', $filters['category_id']);
        if (isset($filters['category_is_anchor'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('cat_index.is_parent=?', $filters['category_is_anchor']);
        }

        $joinCond = join(' AND ', $conditions);
        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_index' => $this->getTable('catalog_category_product_index')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }

        $this->_productLimitationJoinStore();

        Mage::dispatchEvent('catalog_product_collection_apply_limitations_after', array(
            'collection'    => $this
        ));

        return $this;
    }

    /**
     * Apply limitation filters to collection base on API
     * Method allows using one time category product table
     * for combinations of category_id filter states
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _applyZeroStoreProductLimitations()
    {
        $filters = $this->_productLimitationFilters;

        $conditions = array(
            'cat_pro.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_pro.category_id=?', $filters['category_id'])
        );
        $joinCond = join(' AND ', $conditions);

        $fromPart = $this->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['cat_pro'])) {
            $fromPart['cat_pro']['joinCondition'] = $joinCond;
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        else {
            $this->getSelect()->join(
                array('cat_pro' => $this->getTable('catalog_category_product')),
                $joinCond,
                array('cat_index_position' => 'position')
            );
        }
        $this->_joinFields['position'] = array(
            'table' => 'cat_pro',
            'field' => 'position',
        );

        return $this;
    }

    /**
     * Add category ids to loaded items
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addCategoryIds()
    {
        if ($this->getFlag('category_ids_added')) {
            return $this;
        }
        $ids = array_keys($this->_items);
        if (empty($ids)) {
            return $this;
        }

        $select = $this->getConnection()->select();

        $select->from($this->_productCategoryTable, array('product_id', 'category_id'));
        $select->where('product_id IN (?)', $ids);

        $data = $this->getConnection()->fetchAll($select);

        $categoryIds = array();
        foreach ($data as $info) {
            if (isset($categoryIds[$info['product_id']])) {
                $categoryIds[$info['product_id']][] = $info['category_id'];
            } else {
                $categoryIds[$info['product_id']] = array($info['category_id']);
            }
        }


        foreach ($this->getItems() as $item) {
            $productId = $item->getId();
            if (isset($categoryIds[$productId])) {
                $item->setCategoryIds($categoryIds[$productId]);
            } else {
                $item->setCategoryIds(array());
            }
        }

        $this->setFlag('category_ids_added', true);
        return $this;
    }

    /**
     * Add tier price data to loaded items
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addTierPriceData()
    {
        if ($this->getFlag('tier_price_added')) {
            return $this;
        }

        $tierPrices = array();
        $productIds = array();
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getId();
            $tierPrices[$item->getId()] = array();
        }
        if (!$productIds) {
            return $this;
        }

        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $this->getAttribute('tier_price');
        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } else if ($this->getStoreId()) {
            $websiteId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        }

        $adapter   = $this->getConnection();
        $columns   = array(
            'price_id'      => 'value_id',
            'website_id'    => 'website_id',
            'all_groups'    => 'all_groups',
            'cust_group'    => 'customer_group_id',
            'price_qty'     => 'qty',
            'price'         => 'value',
            'product_id'    => 'entity_id'
        );
        $select  = $adapter->select()
            ->from($this->getTable('catalog_product_entity_tier_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order(array('entity_id','qty'));

        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }

        foreach ($adapter->fetchAll($select) as $row) {
            $tierPrices[$row['product_id']][] = array(
                'website_id'    => $row['website_id'],
                'cust_group'    => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL : $row['cust_group'],
                'price_qty'     => $row['price_qty'],
                'price'         => $row['price'],
                'website_price' => $row['price'],

            );
        }

        /* @var $backend Mage_Catalog_Model_Product_Attribute_Backend_Tierprice */
        $backend = $attribute->getBackend();

        foreach ($this->getItems() as $item) {
            $data = $tierPrices[$item->getId()];
            if (!empty($data) && $websiteId) {
                $data = $backend->preparePriceData($data, $item->getTypeId(), $websiteId);
            }
            $item->setData('tier_price', $data);
        }

        $this->setFlag('tier_price_added', true);
        return $this;
    }

    /**
     * Add field comparison expression
     *
     * @param string $comparisonFormat - expression for sprintf()
     * @param array $fields - list of fields
     * @return Mage_Catalog_Model_Resource_Product_Collection
     * @throws Exception
     */
    public function addPriceDataFieldFilter($comparisonFormat, $fields)
    {
        if (!preg_match('/^%s( (<|>|=|<=|>=|<>) %s)*$/', $comparisonFormat)) {
            throw new Exception('Invalid comparison format.');
        }

        if (!is_array($fields)) {
            $fields = array($fields);
        }
        foreach ($fields as $key => $field) {
            $fields[$key] = $this->_getMappedField($field);
        }

        $this->_priceDataFieldFilters[] = array_merge(array($comparisonFormat), $fields);
        return $this;
    }

    /**
     * Clear collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function clear()
    {
        foreach ($this->_items as $i => $item) {
            if ($item->hasStockItem()) {
                $item->unsStockItem();
            }
            $item = $this->_items[$i] = null;
        }

        foreach ($this->_itemsById as $i => $item) {
            $item = $this->_itemsById[$i] = null;
        }

        unset($this->_items, $this->_data, $this->_itemsById);
        $this->_data = array();
        $this->_itemsById = array();
        return parent::clear();
    }

}
