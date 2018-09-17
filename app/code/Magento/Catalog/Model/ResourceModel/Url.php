<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

/**
 * Catalog url rewrite resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class Url
 * @package Magento\Catalog\Model\ResourceModel
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Url extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Stores configuration array
     *
     * @var array
     */
    protected $_stores;

    /**
     * Category attribute properties cache
     *
     * @var array
     */
    protected $_categoryAttributes = [];

    /**
     * Product attribute properties cache
     *
     * @var array
     */
    protected $_productAttributes = [];

    /**
     * Limit products for select
     *
     * @var int
     */
    protected $_productLimit = 250;

    /**
     * Cache of root category children ids
     *
     * @var array
     */
    protected $_rootChildrenIds = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $_catalogCategory;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Product
     */
    protected $productResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Url constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Product $productResource
     * @param \Magento\Catalog\Model\Category $catalogCategory
     * @param \Psr\Log\LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        Product $productResource,
        \Magento\Catalog\Model\Category $catalogCategory,
        \Psr\Log\LoggerInterface $logger,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        $this->productResource = $productResource;
        $this->_catalogCategory = $catalogCategory;
        $this->_logger = $logger;
        parent::__construct($context, $connectionName);
    }

    /**
     * Load core Url rewrite model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('url_rewrite', 'url_rewrite_id');
    }

    /**
     * Retrieve stores array or store model
     *
     * @param int $storeId
     * @return \Magento\Store\Model\Store|\Magento\Store\Model\Store[]
     */
    public function getStores($storeId = null)
    {
        if ($this->_stores === null) {
            $this->_stores = $this->_prepareStoreRootCategories($this->_storeManager->getStores());
        }
        if ($storeId && isset($this->_stores[$storeId])) {
            return $this->_stores[$storeId];
        }
        return $this->_stores;
    }

    /**
     * Retrieve category attributes
     *
     * @param string $attributeCode
     * @param int|array $categoryIds
     * @param int $storeId
     * @return array
     */
    protected function _getCategoryAttribute($attributeCode, $categoryIds, $storeId)
    {
        $linkField = $this->getMetadataPool()->getMetadata(CategoryInterface::class)->getLinkField();
        $identifierFiled = $this->getMetadataPool()->getMetadata(CategoryInterface::class)->getIdentifierField();

        $connection = $this->getConnection();
        if (!isset($this->_categoryAttributes[$attributeCode])) {
            $attribute = $this->_catalogCategory->getResource()->getAttribute($attributeCode);

            $this->_categoryAttributes[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal(),
                'is_static' => $attribute->isStatic(),
            ];
            unset($attribute);
        }

        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        $attributeTable = $this->_categoryAttributes[$attributeCode]['table'];
        $select = $connection->select();
        $bind = [];
        if ($this->_categoryAttributes[$attributeCode]['is_static']) {
            $select->from(
                $this->getTable('catalog_category_entity'),
                ['value' => $attributeCode, 'entity_id' => 'entity_id']
            )->where(
                'entity_id IN(?)',
                $categoryIds
            );
        } elseif ($this->_categoryAttributes[$attributeCode]['is_global'] || $storeId == 0) {
            $select->from(
                ['t1' =>$this->getTable('catalog_category_entity')],
                [$identifierFiled]
            )->joinLeft(
                ['e' => $attributeTable],
                "t1.{$linkField} = e.{$linkField}",
                ['value']
            )->where(
                "t1.{$identifierFiled} IN(?)",
                $categoryIds
            )->where(
                'e.attribute_id = :attribute_id'
            )->where(
                'e.store_id = ?',
                0
            );

            $bind['attribute_id'] = $this->_categoryAttributes[$attributeCode]['attribute_id'];
        } else {
            $valueExpr = $connection->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
            $select->from(
                ['t1' => $attributeTable],
                [$identifierFiled => 'e.'.$identifierFiled, 'value' => $valueExpr]
            )->joinLeft(
                ['t2' => $attributeTable],
                "t1.{$linkField} = t2.{$linkField} AND t1.attribute_id = t2.attribute_id AND t2.store_id = :store_id",
                []
            )->joinLeft(
                ['e' => $this->getTable('catalog_category_entity')],
                "e.{$linkField} = t1.{$linkField}",
                []
            )->where(
                't1.store_id = ?',
                0
            )->where(
                't1.attribute_id = :attribute_id'
            )->where(
                "e.entity_id IN(?)",
                $categoryIds
            )->group('e.entity_id');

            $bind['attribute_id'] = $this->_categoryAttributes[$attributeCode]['attribute_id'];
            $bind['store_id'] = $storeId;
        }

        $rowSet = $connection->fetchAll($select, $bind);

        $attributes = [];
        foreach ($rowSet as $row) {
            $attributes[$row[$identifierFiled]] = $row['value'];
        }
        unset($rowSet);
        foreach ($categoryIds as $categoryId) {
            if (!isset($attributes[$categoryId])) {
                $attributes[$categoryId] = null;
            }
        }

        return $attributes;
    }

    /**
     * Retrieve product attribute
     *
     * @param string $attributeCode
     * @param int|array $productIds
     * @param string $storeId
     * @return array
     */
    public function _getProductAttribute($attributeCode, $productIds, $storeId)
    {
        $connection = $this->getConnection();
        if (!isset($this->_productAttributes[$attributeCode])) {
            $attribute = $this->productResource->getAttribute($attributeCode);

            $this->_productAttributes[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal(),
            ];
            unset($attribute);
        }

        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        $bind = ['attribute_id' => $this->_productAttributes[$attributeCode]['attribute_id']];
        $select = $connection->select();
        $attributeTable = $this->_productAttributes[$attributeCode]['table'];
        if ($this->_productAttributes[$attributeCode]['is_global'] || $storeId == 0) {
            $select->from(
                $attributeTable,
                ['entity_id', 'value']
            )->where(
                'attribute_id = :attribute_id'
            )->where(
                'store_id = ?',
                0
            )->where(
                'entity_id IN(?)',
                $productIds
            );
        } else {
            $valueExpr = $connection->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
            $select->from(
                ['t1' => $attributeTable],
                ['entity_id', 'value' => $valueExpr]
            )->joinLeft(
                ['t2' => $attributeTable],
                't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id AND t2.store_id=:store_id',
                []
            )->where(
                't1.store_id = ?',
                0
            )->where(
                't1.attribute_id = :attribute_id'
            )->where(
                't1.entity_id IN(?)',
                $productIds
            );
            $bind['store_id'] = $storeId;
        }

        $rowSet = $connection->fetchAll($select, $bind);

        $attributes = [];
        foreach ($rowSet as $row) {
            $attributes[$row['entity_id']] = $row['value'];
        }
        unset($rowSet);
        foreach ($productIds as $productId) {
            if (!isset($attributes[$productId])) {
                $attributes[$productId] = null;
            }
        }

        return $attributes;
    }

    /**
     * Prepare category parentId
     *
     * @param \Magento\Framework\DataObject $category
     * @return $this
     */
    protected function _prepareCategoryParentId(\Magento\Framework\DataObject $category)
    {
        if ($category->getPath() != $category->getId()) {
            $split = explode('/', $category->getPath());
            $category->setParentId($split[count($split) - 2]);
        } else {
            $category->setParentId(0);
        }
        return $this;
    }

    /**
     * Prepare stores root categories
     *
     * @param \Magento\Store\Model\Store[] $stores
     * @return \Magento\Store\Model\Store[]
     */
    protected function _prepareStoreRootCategories($stores)
    {
        $rootCategoryIds = [];
        foreach ($stores as $store) {
            /* @var $store \Magento\Store\Model\Store */
            $rootCategoryIds[$store->getRootCategoryId()] = $store->getRootCategoryId();
        }
        if ($rootCategoryIds) {
            $categories = $this->_getCategories($rootCategoryIds);
        }
        foreach ($stores as $store) {
            /* @var $store \Magento\Store\Model\Store */
            $rootCategoryId = $store->getRootCategoryId();
            if (isset($categories[$rootCategoryId])) {
                $store->setRootCategoryPath($categories[$rootCategoryId]->getPath());
                $store->setRootCategory($categories[$rootCategoryId]);
            } else {
                unset($stores[$store->getId()]);
            }
        }
        return $stores;
    }

    /**
     * Retrieve categories objects
     * Either $categoryIds or $path (with ending slash) must be specified
     *
     * @param int|array $categoryIds
     * @param int $storeId
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getCategories($categoryIds, $storeId = null, $path = null)
    {
        $isActiveAttribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Category::ENTITY, 'is_active');
        $categories = [];
        $connection = $this->getConnection();

        $meta = $this->getMetadataPool()->getMetadata(CategoryInterface::class);
        $linkField = $meta->getLinkField();

        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }
        $isActiveExpr = $connection->getCheckSql('c.value_id > 0', 'c.value', 'c.value');
        $select = $connection->select()->from(
            ['main_table' => $this->getTable('catalog_category_entity')],
            [
                'main_table.entity_id',
                'main_table.parent_id',
                'main_table.level',
                'is_active' => $isActiveExpr,
                'main_table.path'
            ]
        );

        // Prepare variables for checking whether categories belong to store
        if ($path === null) {
            $select->where('main_table.entity_id IN(?)', $categoryIds);
        } else {
            // Ensure that path ends with '/', otherwise we can get wrong results - e.g. $path = '1/2' will get '1/20'
            if (substr($path, -1) != '/') {
                $path .= '/';
            }

            $select->where('main_table.path LIKE ?', $path . '%')->order('main_table.path');
        }
        $table = $this->getTable('catalog_category_entity_int');
        $select->joinLeft(
            ['d' => $table],
            "d.attribute_id = :attribute_id AND d.store_id = 0 AND d.{$linkField} = main_table.{$linkField}",
            []
        )->joinLeft(
            ['c' => $table],
            "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = main_table.{$linkField}",
            []
        );

        if ($storeId !== null) {
            $rootCategoryPath = $this->getStores($storeId)->getRootCategoryPath();
            $rootCategoryPathLength = strlen($rootCategoryPath);
        }
        $bind = ['attribute_id' => (int)$isActiveAttribute->getId(), 'store_id' => (int)$storeId];

        $rowSet = $connection->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            if ($storeId !== null) {
                // Check the category to be either store's root or its descendant
                // First - check that category's start is the same as root category
                if (substr($row['path'], 0, $rootCategoryPathLength) != $rootCategoryPath) {
                    continue;
                }
                // Second - check non-root category - that it's really a descendant, not a simple string match
                if (strlen($row['path']) > $rootCategoryPathLength && $row['path'][$rootCategoryPathLength] != '/') {
                    continue;
                }
            }

            $category = new \Magento\Framework\DataObject($row);
            $category->setId($row['entity_id']);
            $category->setEntityId($row['entity_id']);
            $category->setStoreId($storeId);
            $this->_prepareCategoryParentId($category);

            $categories[$category->getId()] = $category;
        }
        unset($rowSet);

        if ($storeId !== null && $categories) {
            foreach (['name', 'url_key', 'url_path'] as $attributeCode) {
                $attributes = $this->_getCategoryAttribute(
                    $attributeCode,
                    array_keys($categories),
                    $category->getStoreId()
                );
                foreach ($attributes as $categoryId => $attributeValue) {
                    $categories[$categoryId]->setData($attributeCode, $attributeValue);
                }
            }
        }

        return $categories;
    }

    /**
     * Retrieve category data object
     *
     * @param int $categoryId
     * @param int $storeId
     * @return \Magento\Framework\DataObject|false
     */
    public function getCategory($categoryId, $storeId)
    {
        if (!$categoryId || !$storeId) {
            return false;
        }

        $categories = $this->_getCategories($categoryId, $storeId);
        if (isset($categories[$categoryId])) {
            return $categories[$categoryId];
        }
        return false;
    }

    /**
     * Retrieve categories data objects by their ids. Return only categories that belong to specified store.
     *
     * @param int|array $categoryIds
     * @param int $storeId
     * @return array|false
     */
    public function getCategories($categoryIds, $storeId)
    {
        if (!$categoryIds || !$storeId) {
            return false;
        }

        return $this->_getCategories($categoryIds, $storeId);
    }

    /**
     * Retrieve Product data objects
     *
     * @param int|array $productIds
     * @param int $storeId
     * @param int $entityId
     * @param int &$lastEntityId
     * @return array
     */
    protected function _getProducts($productIds, $storeId, $entityId, &$lastEntityId)
    {
        $products = [];
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        $connection = $this->getConnection();
        if ($productIds !== null) {
            if (!is_array($productIds)) {
                $productIds = [$productIds];
            }
        }
        $bind = ['website_id' => (int)$websiteId, 'entity_id' => (int)$entityId];
        $select = $connection->select()->useStraightJoin(
            true
        )->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['w' => $this->getTable('catalog_product_website')],
            'e.entity_id = w.product_id AND w.website_id = :website_id',
            []
        )->where(
            'e.entity_id > :entity_id'
        )->order(
            'e.entity_id'
        )->limit(
            $this->_productLimit
        );
        if ($productIds !== null) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $rowSet = $connection->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $product = new \Magento\Framework\DataObject($row);
            $product->setId($row['entity_id']);
            $product->setEntityId($row['entity_id']);
            $product->setCategoryIds([]);
            $product->setStoreId($storeId);
            $products[$product->getId()] = $product;
            $lastEntityId = $product->getId();
        }

        unset($rowSet);

        if ($products) {
            $select = $connection->select()->from(
                $this->getTable('catalog_category_product'),
                ['product_id', 'category_id']
            )->where(
                'product_id IN(?)',
                array_keys($products)
            );
            $categories = $connection->fetchAll($select);
            foreach ($categories as $category) {
                $productId = $category['product_id'];
                $categoryIds = $products[$productId]->getCategoryIds();
                $categoryIds[] = $category['category_id'];
                $products[$productId]->setCategoryIds($categoryIds);
            }

            foreach (['name', 'url_key', 'url_path'] as $attributeCode) {
                $attributes = $this->_getProductAttribute($attributeCode, array_keys($products), $storeId);
                foreach ($attributes as $productId => $attributeValue) {
                    $products[$productId]->setData($attributeCode, $attributeValue);
                }
            }
        }

        return $products;
    }

    /**
     * Retrieve Product data object
     *
     * @param int $productId
     * @param int $storeId
     * @return \Magento\Framework\DataObject|false
     */
    public function getProduct($productId, $storeId)
    {
        $entityId = 0;
        $products = $this->_getProducts($productId, $storeId, 0, $entityId);
        if (isset($products[$productId])) {
            return $products[$productId];
        }
        return false;
    }

    /**
     * Retrieve Product data obects for store
     *
     * @param int $storeId
     * @param int &$lastEntityId
     * @return array
     */
    public function getProductsByStore($storeId, &$lastEntityId)
    {
        return $this->_getProducts(null, $storeId, $lastEntityId, $lastEntityId);
    }

    /**
     * Get rewrite by product store
     *
     * Retrieve rewrites and visibility by store
     * Input array format:
     * product_id as key and store_id as value
     * Output array format (product_id as key)
     * store_id     int; store id
     * visibility   int; visibility for store
     * url_rewrite  string; rewrite URL for store
     *
     * @param array $products
     * @return array
     */
    public function getRewriteByProductStore(array $products)
    {
        $result = [];

        if (empty($products)) {
            return $result;
        }
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['i' => $this->getTable('catalog_category_product_index')],
            ['product_id', 'store_id', 'visibility']
        )->joinLeft(
            ['u' => $this->getMainTable()],
            'i.product_id = u.entity_id AND i.store_id = u.store_id'
            . ' AND u.entity_type = "' . ProductUrlRewriteGenerator::ENTITY_TYPE . '"',
            ['request_path']
        )->joinLeft(
            ['r' => $this->getTable('catalog_url_rewrite_product_category')],
            'u.url_rewrite_id = r.url_rewrite_id AND r.category_id is NULL',
            []
        );

        $bind = [];
        foreach ($products as $productId => $storeId) {
            $catId = $this->_storeManager->getStore($storeId)->getRootCategoryId();
            $productBind = 'product_id' . $productId;
            $storeBind = 'store_id' . $storeId;
            $catBind = 'category_id' . $catId;
            $cond = '(' . implode(
                ' AND ',
                ['i.product_id = :' . $productBind, 'i.store_id = :' . $storeBind, 'i.category_id = :' . $catBind]
            ) . ')';
            $bind[$productBind] = $productId;
            $bind[$storeBind] = $storeId;
            $bind[$catBind] = $catId;
            $select->orWhere($cond);
        }

        $rowSet = $connection->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $result[$row['product_id']] = [
                'store_id' => $row['store_id'],
                'visibility' => $row['visibility'],
                'url_rewrite' => $row['request_path'],
            ];
        }

        return $result;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }
}
