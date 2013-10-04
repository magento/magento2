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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Resource;

/**
 * Product entity resource model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Product extends \Magento\Catalog\Model\Resource\AbstractResource
{
    /**
     * Product to website linkage table
     *
     * @var string
     */
    protected $_productWebsiteTable;

    /**
     * Product to category linkage table
     *
     * @var string
     */
    protected $_productCategoryTable;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\Resource\Category
     */
    protected $_catalogCategory;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Category $catalogCategory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\Resource\Category $catalogCategory,
        $data = array()
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_catalogCategory = $catalogCategory;
        parent::__construct(
            $resource,
            $eavConfig,
            $attrSetEntity,
            $locale,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $modelFactory,
            $data
        );
        $this->setType(\Magento\Catalog\Model\Product::ENTITY)->setConnection('catalog_read', 'catalog_write');
        $this->_productWebsiteTable = $this->getTable('catalog_product_website');
        $this->_productCategoryTable = $this->getTable('catalog_category_product');
    }

    /**
     * Default product attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return array('entity_id', 'entity_type_id', 'attribute_set_id', 'type_id', 'created_at', 'updated_at');
    }

    /**
     * Retrieve product website identifiers
     *
     * @param \Magento\Catalog\Model\Product|int $product
     * @return array
     */
    public function getWebsiteIds($product)
    {
        $adapter = $this->_getReadAdapter();

        if ($product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        $select = $adapter->select()
            ->from($this->_productWebsiteTable, 'website_id')
            ->where('product_id = ?', (int)$productId);

        return $adapter->fetchCol($select);
    }

    /**
     * Retrieve product website identifiers by product identifiers
     *
     * @param   array $productIds
     * @return  array
     */
    public function getWebsiteIdsByProductIds($productIds)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_productWebsiteTable, array('product_id', 'website_id'))
            ->where('product_id IN (?)', $productIds);
        $productsWebsites = array();
        foreach ($this->_getWriteAdapter()->fetchAll($select) as $productInfo) {
            $productId = $productInfo['product_id'];
            if (!isset($productsWebsites[$productId])) {
                $productsWebsites[$productId] = array();
            }
            $productsWebsites[$productId][] = $productInfo['website_id'];

        }

        return $productsWebsites;
    }

    /**
     * Retrieve product category identifiers
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getCategoryIds($product)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_productCategoryTable, 'category_id')
            ->where('product_id = ?', (int)$product->getId());

        return $adapter->fetchCol($select);
    }

    /**
     * Get product identifier by sku
     *
     * @param string $sku
     * @return int|false
     */
    public function getIdBySku($sku)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('sku = :sku');

        $bind = array(':sku' => (string)$sku);

        return $adapter->fetchOne($select, $bind);
    }

    /**
     * Process product data before save
     *
     * @param \Magento\Object $object
     * @return \Magento\Catalog\Model\Resource\Product
     */
    protected function _beforeSave(\Magento\Object $object)
    {
        /**
         * Check if declared category ids in object data.
         */
        if ($object->hasCategoryIds()) {
            $categoryIds = $this->_catalogCategory->verifyIds(
                $object->getCategoryIds()
            );
            $object->setCategoryIds($categoryIds);
        }

        $self = parent::_beforeSave($object);
        /**
         * Try detect product id by sku if id is not declared
         */
        if (!$object->getId() && $object->getSku()) {
            $object->setId($this->getIdBySku($object->getSku()));
        }
        return $self;
    }

    /**
     * Save data related with product
     *
     * @param \Magento\Object $product
     * @return \Magento\Catalog\Model\Resource\Product
     */
    protected function _afterSave(\Magento\Object $product)
    {
        $this->_saveWebsiteIds($product)
            ->_saveCategories($product);
        return parent::_afterSave($product);
    }

    /**
     * Save product website relations
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Resource\Product
     */
    protected function _saveWebsiteIds($product)
    {
        $websiteIds = $product->getWebsiteIds();
        $oldWebsiteIds = array();

        $product->setIsChangedWebsites(false);

        $adapter = $this->_getWriteAdapter();

        $oldWebsiteIds = $this->getWebsiteIds($product);

        $insert = array_diff($websiteIds, $oldWebsiteIds);
        $delete = array_diff($oldWebsiteIds, $websiteIds);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $websiteId) {
                $data[] = array(
                    'product_id' => (int)$product->getId(),
                    'website_id' => (int)$websiteId
                );
            }
            $adapter->insertMultiple($this->_productWebsiteTable, $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $websiteId) {
                $condition = array(
                    'product_id = ?' => (int)$product->getId(),
                    'website_id = ?' => (int)$websiteId,
                );

                $adapter->delete($this->_productWebsiteTable, $condition);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $product->setIsChangedWebsites(true);
        }

        return $this;
    }

    /**
     * Save product category relations
     *
     * @param \Magento\Object $object
     * @return \Magento\Catalog\Model\Resource\Product
     */
    protected function _saveCategories(\Magento\Object $object)
    {
        /**
         * If category ids data is not declared we haven't do manipulations
         */
        if (!$object->hasCategoryIds()) {
            return $this;
        }
        $categoryIds = $object->getCategoryIds();
        $oldCategoryIds = $this->getCategoryIds($object);

        $object->setIsChangedCategories(false);

        $insert = array_diff($categoryIds, $oldCategoryIds);
        $delete = array_diff($oldCategoryIds, $categoryIds);

        $write = $this->_getWriteAdapter();
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $categoryId) {
                if (empty($categoryId)) {
                    continue;
                }
                $data[] = array(
                    'category_id' => (int)$categoryId,
                    'product_id'  => (int)$object->getId(),
                    'position'    => 1
                );
            }
            if ($data) {
                $write->insertMultiple($this->_productCategoryTable, $data);
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $categoryId) {
                $where = array(
                    'product_id = ?'  => (int)$object->getId(),
                    'category_id = ?' => (int)$categoryId,
                );

                $write->delete($this->_productCategoryTable, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $object->setAffectedCategoryIds(array_merge($insert, $delete));
            $object->setIsChangedCategories(true);
        }

        return $this;
    }

    /**
     * Refresh Product Enabled Index
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Resource\Product
     */
    public function refreshIndex($product)
    {
        $writeAdapter = $this->_getWriteAdapter();

        /**
         * Ids of all categories where product is assigned (not related with store)
         */
        $categoryIds = $product->getCategoryIds();

        /**
         * Clear previous index data related with product
         */
        $condition = array('product_id = ?' => (int)$product->getId());
        $writeAdapter->delete($this->getTable('catalog_category_product_index'), $condition);

        if (!empty($categoryIds)) {
            $categoriesSelect = $writeAdapter->select()
                ->from($this->getTable('catalog_category_entity'))
                ->where('entity_id IN (?)', $categoryIds);

            $categoriesInfo = $writeAdapter->fetchAll($categoriesSelect);

            $indexCategoryIds = array();
            foreach ($categoriesInfo as $categoryInfo) {
                $ids = explode('/', $categoryInfo['path']);
                $ids[] = $categoryInfo['entity_id'];
                $indexCategoryIds = array_merge($indexCategoryIds, $ids);
            }

            $indexCategoryIds   = array_unique($indexCategoryIds);
            $indexProductIds    = array($product->getId());

            $this->_catalogCategory->refreshProductIndex($indexCategoryIds, $indexProductIds);
        } else {
            $websites = $product->getWebsiteIds();

            if ($websites) {
                $storeIds = array();

                foreach ($websites as $websiteId) {
                    $website  = $this->_storeManager->getWebsite($websiteId);
                    $storeIds = array_merge($storeIds, $website->getStoreIds());
                }

                $this->_catalogCategory->refreshProductIndex(array(), array($product->getId()), $storeIds);
            }
        }

        /**
         * Refresh enabled products index (visibility state)
         */
        $this->refreshEnabledIndex(null, $product);

        return $this;
    }

    /**
     * Refresh index for visibility of enabled product in store
     * if store parameter is null - index will refreshed for all stores
     * if product parameter is null - idex will be refreshed for all products
     *
     * @param \Magento\Core\Model\Store $store
     * @param \Magento\Catalog\Model\Product $product
     * @throws \Magento\Core\Exception
     * @return \Magento\Catalog\Model\Resource\Product
     */
    public function refreshEnabledIndex($store = null, $product = null)
    {
        $statusAttribute        = $this->getAttribute('status');
        $visibilityAttribute    = $this->getAttribute('visibility');
        $statusAttributeId      = $statusAttribute->getId();
        $visibilityAttributeId  = $visibilityAttribute->getId();
        $statusTable            = $statusAttribute->getBackend()->getTable();
        $visibilityTable        = $visibilityAttribute->getBackend()->getTable();

        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select();
        $condition = array();

        $indexTable = $this->getTable('catalog_product_enabled_index');
        if (is_null($store) && is_null($product)) {
            throw new \Magento\Core\Exception(
                __('To reindex the enabled product(s), please specify the store or product.')
            );
        } elseif (is_null($product) || is_array($product)) {
            $storeId    = $store->getId();
            $websiteId  = $store->getWebsiteId();

            if (is_array($product) && !empty($product)) {
                $condition[] = $adapter->quoteInto('product_id IN (?)', $product);
            }

            $condition[] = $adapter->quoteInto('store_id = ?', $storeId);

            $selectFields = array(
                't_v_default.entity_id',
                new \Zend_Db_Expr($storeId),
                $adapter->getCheckSql('t_v.value_id > 0', 't_v.value', 't_v_default.value'),
            );

            $select->joinInner(
                array('w' => $this->getTable('catalog_product_website')),
                $adapter->quoteInto(
                    'w.product_id = t_v_default.entity_id AND w.website_id = ?', $websiteId
                ),
                array()
            );
        } elseif ($store === null) {
            foreach ($product->getStoreIds() as $storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $this->refreshEnabledIndex($store, $product);
            }
            return $this;
        } else {
            $productId = is_numeric($product) ? $product : $product->getId();
            $storeId   = is_numeric($store) ? $store : $store->getId();

            $condition = array(
                'product_id = ?' => (int)$productId,
                'store_id   = ?' => (int)$storeId,
            );

            $selectFields = array(
                new \Zend_Db_Expr($productId),
                new \Zend_Db_Expr($storeId),
                $adapter->getCheckSql('t_v.value_id > 0', 't_v.value', 't_v_default.value')
            );

            $select->where('t_v_default.entity_id = ?', $productId);
        }

        $adapter->delete($indexTable, $condition);

        $select->from(array('t_v_default' => $visibilityTable), $selectFields);

        $visibilityTableJoinCond = array(
            't_v.entity_id = t_v_default.entity_id',
            $adapter->quoteInto('t_v.attribute_id = ?', $visibilityAttributeId),
            $adapter->quoteInto('t_v.store_id     = ?', $storeId),
        );

        $select->joinLeft(
            array('t_v' => $visibilityTable),
            implode(' AND ', $visibilityTableJoinCond),
            array()
        );

        $defaultStatusJoinCond = array(
            't_s_default.entity_id = t_v_default.entity_id',
            't_s_default.store_id = 0',
            $adapter->quoteInto('t_s_default.attribute_id = ?', $statusAttributeId),
        );

        $select->joinInner(
            array('t_s_default' => $statusTable),
            implode(' AND ', $defaultStatusJoinCond),
            array()
        );


        $statusJoinCond = array(
            't_s.entity_id = t_v_default.entity_id',
            $adapter->quoteInto('t_s.store_id     = ?', $storeId),
            $adapter->quoteInto('t_s.attribute_id = ?', $statusAttributeId),
        );

        $select->joinLeft(
            array('t_s' => $statusTable),
            implode(' AND ', $statusJoinCond),
            array()
        );

        $valueCondition = $adapter->getCheckSql('t_s.value_id > 0', 't_s.value', 't_s_default.value');

        $select->where('t_v_default.attribute_id = ?', $visibilityAttributeId)
            ->where('t_v_default.store_id = ?', 0)
            ->where(sprintf('%s = ?', $valueCondition), \Magento\Catalog\Model\Product\Status::STATUS_ENABLED);

        if (is_array($product) && !empty($product)) {
            $select->where('t_v_default.entity_id IN (?)', $product);
        }

        $adapter->query($adapter->insertFromSelect($select, $indexTable));


        return $this;
    }

    /**
     * Get collection of product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Resource\Category\Collection
     */
    public function getCategoryCollection($product)
    {
        /** @var \Magento\Catalog\Model\Resource\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->joinField('product_id',
                'catalog_category_product',
                'product_id',
                'category_id = entity_id',
                null)
            ->addFieldToFilter('product_id', (int)$product->getId());
        return $collection;
    }

    /**
     * Retrieve category ids where product is available
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return array
     */
    public function getAvailableInCategories($object)
    {
        // is_parent=1 ensures that we'll get only category IDs those are direct parents of the product, instead of
        // fetching all parent IDs, including those are higher on the tree
        $select = $this->_getReadAdapter()->select()->distinct()
            ->from($this->getTable('catalog_category_product_index'), array('category_id'))
            ->where('product_id = ? AND is_parent = 1', (int)$object->getEntityId());

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return 'Magento\Eav\Model\Entity\Attribute\Source\Table';
    }

    /**
     * Check availability display product in category
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $categoryId
     * @return string
     */
    public function canBeShowInCategory($product, $categoryId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('catalog_category_product_index'), 'product_id')
            ->where('product_id = ?', (int)$product->getId())
            ->where('category_id = ?', (int)$categoryId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Duplicate product store values
     *
     * @param int $oldId
     * @param int $newId
     * @return \Magento\Catalog\Model\Resource\Product
     */
    public function duplicate($oldId, $newId)
    {
        $adapter = $this->_getWriteAdapter();
        $eavTables = array('datetime', 'decimal', 'int', 'text', 'varchar');

        $adapter = $this->_getWriteAdapter();

        // duplicate EAV store values
        foreach ($eavTables as $suffix) {
            $tableName = $this->getTable(array('catalog_product_entity', $suffix));

            $select = $adapter->select()
                ->from($tableName, array(
                    'entity_type_id',
                    'attribute_id',
                    'store_id',
                    'entity_id' => new \Zend_Db_Expr($adapter->quote($newId)),
                    'value'
                ))
                ->where('entity_id = ?', $oldId)
                ->where('store_id > ?', 0);

            $adapter->query($adapter->insertFromSelect(
                $select,
                $tableName,
                array(
                    'entity_type_id',
                    'attribute_id',
                    'store_id',
                    'entity_id',
                    'value'
                ),
                \Magento\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            ));
        }

        // set status as disabled
        $statusAttribute      = $this->getAttribute('status');
        $statusAttributeId    = $statusAttribute->getAttributeId();
        $statusAttributeTable = $statusAttribute->getBackend()->getTable();
        $updateCond[]         = 'store_id > 0';
        $updateCond[]         = $adapter->quoteInto('entity_id = ?', $newId);
        $updateCond[]         = $adapter->quoteInto('attribute_id = ?', $statusAttributeId);
        $adapter->update(
            $statusAttributeTable,
            array('value' => \Magento\Catalog\Model\Product\Status::STATUS_DISABLED),
            $updateCond
        );

        return $this;
    }

    /**
     * Get SKU through product identifiers
     *
     * @param  array $productIds
     * @return array
     */
    public function getProductsSku(array $productIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('catalog_product_entity'), array('entity_id', 'sku'))
            ->where('entity_id IN (?)', $productIds);
        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * Retrieve product entities info
     *
     * @param  array|string|null $columns
     * @return array
     */
    public function getProductEntitiesInfo($columns = null)
    {
        if (!empty($columns) && is_string($columns)) {
            $columns = array($columns);
        }
        if (empty($columns) || !is_array($columns)) {
            $columns = $this->_getDefaultAttributes();
        }

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('catalog_product_entity'), $columns);

        return $adapter->fetchAll($select);
    }

    /**
     * Return assigned images for specific stores
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int|array $storeIds
     * @return array
     *
     */
    public function getAssignedImages($product, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $mainTable = $product->getResource()->getAttribute('image')
            ->getBackend()
            ->getTable();
        $read      = $this->_getReadAdapter();
        $select    = $read->select()
            ->from(
                array('images' => $mainTable),
                array('value as filepath', 'store_id')
            )
            ->joinLeft(
                array('attr' => $this->getTable('eav_attribute')),
                'images.attribute_id = attr.attribute_id',
                array('attribute_code')
            )
            ->where('entity_id = ?', $product->getId())
            ->where('store_id IN (?)', $storeIds)
            ->where('attribute_code IN (?)', array('small_image', 'thumbnail', 'image'));

        $images = $read->fetchAll($select);
        return $images;
    }

    /**
     * Get total number of records in the system
     *
     * @return int
     */
    public function countAll()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();
        $select->from($this->getEntityTable(), 'COUNT(*)');
        $result = (int)$adapter->fetchOne($select);
        return $result;
    }
}
