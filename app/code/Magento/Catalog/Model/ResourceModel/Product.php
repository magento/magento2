<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

/**
 * Product entity resource model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends AbstractResource
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
     * @var Category
     */
    protected $_catalogCategory;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\DefaultAttributes
     */
    protected $defaultAttributes;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param Category\CollectionFactory $categoryCollectionFactory
     * @param Category $catalogCategory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Entity\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        Category $catalogCategory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes,
        $data = []
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_catalogCategory = $catalogCategory;
        $this->eventManager = $eventManager;
        $this->setFactory = $setFactory;
        $this->typeFactory = $typeFactory;
        $this->defaultAttributes = $defaultAttributes;
        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $data
        );
        $this->connectionName  = 'catalog';
    }

    /**
     * Entity type getter and lazy loader
     *
     * @return \Magento\Eav\Model\Entity\Type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType(\Magento\Catalog\Model\Product::ENTITY);
        }
        return parent::getEntityType();
    }

    /**
     * Product Website table name getter
     *
     * @return string
     */
    public function getProductWebsiteTable()
    {
        if (!$this->_productWebsiteTable) {
            $this->_productWebsiteTable = $this->getTable('catalog_product_website');
        }
        return $this->_productWebsiteTable;
    }

    /**
     * Product Category table name getter
     *
     * @return string
     */
    public function getProductCategoryTable()
    {
        if (!$this->_productCategoryTable) {
            $this->_productCategoryTable = $this->getTable('catalog_category_product');
        }
        return $this->_productCategoryTable;
    }

    /**
     * Default product attributes
     *
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return $this->defaultAttributes->getDefaultAttributes();
    }

    /**
     * Retrieve product website identifiers
     *
     * @param \Magento\Catalog\Model\Product|int $product
     * @return array
     */
    public function getWebsiteIds($product)
    {
        $connection = $this->getConnection();

        if ($product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        $select = $connection->select()->from(
            $this->getProductWebsiteTable(),
            'website_id'
        )->where(
            'product_id = ?',
            (int)$productId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Retrieve product website identifiers by product identifiers
     *
     * @param   array $productIds
     * @return  array
     */
    public function getWebsiteIdsByProductIds($productIds)
    {
        $select = $this->getConnection()->select()->from(
            $this->getProductWebsiteTable(),
            ['product_id', 'website_id']
        )->where(
            'product_id IN (?)',
            $productIds
        );
        $productsWebsites = [];
        foreach ($this->getConnection()->fetchAll($select) as $productInfo) {
            $productId = $productInfo['product_id'];
            if (!isset($productsWebsites[$productId])) {
                $productsWebsites[$productId] = [];
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
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getProductCategoryTable(),
            'category_id'
        )->where(
            'product_id = ?',
            (int)$product->getId()
        );

        return $connection->fetchCol($select);
    }

    /**
     * Get product identifier by sku
     *
     * @param string $sku
     * @return int|false
     */
    public function getIdBySku($sku)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getEntityTable(), 'entity_id')->where('sku = :sku');

        $bind = [':sku' => (string)$sku];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Process product data before save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\DataObject $object)
    {
        /**
         * Check if declared category ids in object data.
         */
        if ($object->hasCategoryIds()) {
            $categoryIds = $this->_catalogCategory->verifyIds($object->getCategoryIds());
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
     * @param \Magento\Framework\DataObject $product
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $product)
    {
        $this->_saveWebsiteIds($product)->_saveCategories($product);
        return parent::_afterSave($product);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $result = parent::delete($object);
        $this->eventManager->dispatch(
            'catalog_product_delete_after_done',
            ['product' => $object]
        );
        return $result;
    }

    /**
     * Save product website relations
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function _saveWebsiteIds($product)
    {
        $websiteIds = $product->getWebsiteIds();
        $oldWebsiteIds = [];

        $product->setIsChangedWebsites(false);

        $connection = $this->getConnection();

        $oldWebsiteIds = $this->getWebsiteIds($product);

        $insert = array_diff($websiteIds, $oldWebsiteIds);
        $delete = array_diff($oldWebsiteIds, $websiteIds);

        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $websiteId) {
                $data[] = ['product_id' => (int)$product->getId(), 'website_id' => (int)$websiteId];
            }
            $connection->insertMultiple($this->getProductWebsiteTable(), $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $websiteId) {
                $condition = ['product_id = ?' => (int)$product->getId(), 'website_id = ?' => (int)$websiteId];

                $connection->delete($this->getProductWebsiteTable(), $condition);
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
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _saveCategories(\Magento\Framework\DataObject $object)
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

        $connection = $this->getConnection();
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryId) {
                if (empty($categoryId)) {
                    continue;
                }
                $data[] = [
                    'category_id' => (int)$categoryId,
                    'product_id' => (int)$object->getId(),
                    'position' => 1,
                ];
            }
            if ($data) {
                $connection->insertMultiple($this->getProductCategoryTable(), $data);
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $categoryId) {
                $where = ['product_id = ?' => (int)$object->getId(), 'category_id = ?' => (int)$categoryId];

                $connection->delete($this->getProductCategoryTable(), $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $object->setAffectedCategoryIds(array_merge($insert, $delete));
            $object->setIsChangedCategories(true);
        }

        return $this;
    }

    /**
     * Get collection of product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection($product)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->joinField(
            'product_id',
            'catalog_category_product',
            'product_id',
            'category_id = entity_id',
            null
        )->addFieldToFilter(
            'product_id',
            (int)$product->getId()
        );
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
        $select = $this->getConnection()->select()->distinct()->from(
            $this->getTable('catalog_category_product_index'),
            ['category_id']
        )->where(
            'product_id = ? AND is_parent = 1',
            (int)$object->getEntityId()
        )->where(
            'visibility != ?',
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
        );

        return $this->getConnection()->fetchCol($select);
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
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_category_product_index'),
            'product_id'
        )->where(
            'product_id = ?',
            (int)$product->getId()
        )->where(
            'category_id = ?',
            (int)$categoryId
        );

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Duplicate product store values
     *
     * @param int $oldId
     * @param int $newId
     * @return $this
     */
    public function duplicate($oldId, $newId)
    {
        $eavTables = ['datetime', 'decimal', 'int', 'text', 'varchar'];
        $connection = $this->getConnection();

        // duplicate EAV store values
        foreach ($eavTables as $suffix) {
            $tableName = $this->getTable(['catalog_product_entity', $suffix]);

            $select = $connection->select()->from(
                $tableName,
                [
                    'attribute_id',
                    'store_id',
                    'entity_id' => new \Zend_Db_Expr($connection->quote($newId)),
                    'value'
                ]
            )->where(
                'entity_id = ?',
                $oldId
            )->where(
                'store_id > ?',
                0
            );

            $connection->query(
                $connection->insertFromSelect(
                    $select,
                    $tableName,
                    ['attribute_id', 'store_id', 'entity_id', 'value'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }

        // set status as disabled
        $statusAttribute = $this->getAttribute('status');
        $statusAttributeId = $statusAttribute->getAttributeId();
        $statusAttributeTable = $statusAttribute->getBackend()->getTable();
        $updateCond[] = 'store_id > 0';
        $updateCond[] = $connection->quoteInto('entity_id = ?', $newId);
        $updateCond[] = $connection->quoteInto('attribute_id = ?', $statusAttributeId);
        $connection->update(
            $statusAttributeTable,
            ['value' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED],
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
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_entity'),
            ['entity_id', 'sku']
        )->where(
            'entity_id IN (?)',
            $productIds
        );
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Get product ids by their sku
     *
     * @param  array $productSkuList
     * @return array
     */
    public function getProductsIdsBySkus(array $productSkuList)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_entity'),
            ['sku', 'entity_id']
        )->where(
            'sku IN (?)',
            $productSkuList
        );

        $result = [];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $result[$row['sku']] = $row['entity_id'];
        }
        return $result;
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
            $columns = [$columns];
        }
        if (empty($columns) || !is_array($columns)) {
            $columns = $this->_getDefaultAttributes();
        }

        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('catalog_product_entity'), $columns);

        return $connection->fetchAll($select);
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
            $storeIds = [$storeIds];
        }

        $mainTable = $product->getResource()->getAttribute('image')->getBackend()->getTable();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['images' => $mainTable],
            ['value as filepath', 'store_id']
        )->joinLeft(
            ['attr' => $this->getTable('eav_attribute')],
            'images.attribute_id = attr.attribute_id',
            ['attribute_code']
        )->where(
            'entity_id = ?',
            $product->getId()
        )->where(
            'store_id IN (?)',
            $storeIds
        )->where(
            'attribute_code IN (?)',
            ['small_image', 'thumbnail', 'image']
        );

        $images = $connection->fetchAll($select);
        return $images;
    }

    /**
     * Get total number of records in the system
     *
     * @return int
     */
    public function countAll()
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getEntityTable(), 'COUNT(*)');
        $result = (int)$connection->fetchOne($select);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object)
    {
        //validate attribute set entity type
        $entityType = $this->typeFactory->create()->loadByCode(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSet = $this->setFactory->create()->load($object->getAttributeSetId());
        if ($attributeSet->getEntityTypeId() != $entityType->getId()) {
            return ['attribute_set' => 'Invalid attribute set entity type'];
        }

        return parent::validate($object);
    }
}
