<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product as ProductEntity;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Product entity resource model
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:disable Magento2.Annotation.MethodAnnotationStructure
 * @since 100.0.2
 */
class Product extends AbstractResource implements ResetAfterRequestInterface
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
     * @var Category
     */
    protected $_catalogCategory;

    /**
     * @var Category\CollectionFactory
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
     * @var EntityManager
     * @since 101.0.0
     */
    protected $entityManager;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\DefaultAttributes
     */
    protected $defaultAttributes;

    /**
     * @var array
     * @since 101.0.0
     */
    protected $availableCategoryIdsCache = [];

    /**
     * @var Product\CategoryLink
     */
    private $productCategoryLink;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var AttributeManagementInterface
     */
    private $eavAttributeManagement;

    /**
     * @var MediaImageDeleteProcessor
     */
    private $mediaImageDeleteProcessor;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

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
     * @param TableMaintainer|null $tableMaintainer
     * @param UniqueValidationInterface|null $uniqueValidator
     * @param AttributeManagementInterface|null $eavAttributeManagement
     * @param MediaImageDeleteProcessor|null $mediaImageDeleteProcessor
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        Category\CollectionFactory $categoryCollectionFactory,
        Category $catalogCategory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes,
        $data = [],
        ?TableMaintainer $tableMaintainer = null,
        ?UniqueValidationInterface $uniqueValidator = null,
        ?AttributeManagementInterface $eavAttributeManagement = null,
        ?MediaImageDeleteProcessor $mediaImageDeleteProcessor = null,
        ?ScopeOverriddenValue $scopeOverriddenValue = null
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
            $data,
            $uniqueValidator
        );
        $this->connectionName  = 'catalog';
        $this->tableMaintainer = $tableMaintainer ?: ObjectManager::getInstance()->get(TableMaintainer::class);
        $this->eavAttributeManagement = $eavAttributeManagement
            ?? ObjectManager::getInstance()->get(AttributeManagementInterface::class);
        $this->mediaImageDeleteProcessor = $mediaImageDeleteProcessor
            ?? ObjectManager::getInstance()->get(MediaImageDeleteProcessor::class);
        $this->scopeOverriddenValue = $scopeOverriddenValue
            ?? ObjectManager::getInstance()->get(ScopeOverriddenValue::class);
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
     * @deprecated 102.0.0
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
     * @deprecated 102.0.0
     * @param \Magento\Catalog\Model\Product|int $product
     * @return array
     */
    public function getWebsiteIds($product)
    {
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product->getEntityId();
        } else {
            $productId = $product;
        }

        return $this->getProductWebsiteLink()->getWebsiteIdsByProductId($productId);
    }

    /**
     * Retrieve product website identifiers by product identifiers
     *
     * @param   array $productIds
     * @return  array
     */
    public function getWebsiteIdsByProductIds($productIds)
    {
        if (!is_array($productIds) || empty($productIds)) {
            return [];
        }
        $select = $this->getConnection()->select()->from(
            $this->getProductWebsiteTable(),
            ['product_id', 'website_id']
        )->where(
            'product_id IN (?)',
            $productIds,
            \Zend_Db::INT_TYPE
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
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getCategoryIds($product)
    {
        $result = $this->getProductCategoryLink()->getCategoryLinks($product);
        return array_column($result, 'category_id');
    }

    /**
     * Get product identifier by sku
     *
     * @param  string $sku
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
     * @param DataObject $object
     * @return $this
     */
    protected function _beforeSave(DataObject $object)
    {
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
     * @param DataObject $product
     * @return $this
     */
    protected function _afterSave(DataObject $product)
    {
        $this->removeNotInSetAttributeValues($product);
        $this->_saveWebsiteIds($product)->_saveCategories($product);
        $this->scopeOverriddenValue->clearAttributesValues(ProductInterface::class, $product);
        return parent::_afterSave($product);
    }

    /**
     * Remove attribute values that absent in product attribute set
     *
     * @param DataObject $product
     * @return DataObject
     */
    private function removeNotInSetAttributeValues(DataObject $product): DataObject
    {
        $oldAttributeSetId = $product->getOrigData(ProductEntity::ATTRIBUTE_SET_ID);
        if ($oldAttributeSetId && $product->dataHasChangedFor(ProductEntity::ATTRIBUTE_SET_ID)) {
            $newAttributes = $product->getAttributes();
            $newAttributesCodes = array_keys($newAttributes);
            $oldAttributes = $this->eavAttributeManagement->getAttributes(
                ProductEntity::ENTITY,
                $oldAttributeSetId
            );
            $oldAttributesCodes = [];
            foreach ($oldAttributes as $oldAttribute) {
                $oldAttributesCodes[] = $oldAttribute->getAttributecode();
            }
            $notInSetAttributeCodes = array_diff($oldAttributesCodes, $newAttributesCodes);
            if (!empty($notInSetAttributeCodes)) {
                $this->deleteSelectedEntityAttributeRows($product, $notInSetAttributeCodes);
            }
        }

        return $product;
    }

    /**
     * Clear selected entity attribute rows
     *
     * @param DataObject $product
     * @param array $attributeCodes
     * @return void
     */
    private function deleteSelectedEntityAttributeRows(DataObject $product, array $attributeCodes): void
    {
        $backendTables = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->getAttribute($attributeCode);
            $backendTable = $attribute->getBackendTable();
            if (!$attribute->isStatic() && $backendTable) {
                $backendTables[$backendTable][] = $attribute->getId();
            }
        }

        $entityIdField = $this->getLinkField();
        $entityId = $product->getData($entityIdField);
        foreach ($backendTables as $backendTable => $attributes) {
            $connection = $this->getConnection();
            $where = $connection->quoteInto('attribute_id IN (?)', $attributes, \Zend_Db::INT_TYPE);
            $where .= $connection->quoteInto(" AND {$entityIdField} = ?", $entityId);
            $connection->delete($backendTable, $where);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($object)
    {
        $this->getEntityManager()->delete($object);
        $this->eventManager->dispatch(
            'catalog_product_delete_after_done',
            ['product' => $object]
        );
        return $this;
    }

    /**
     * Save product website relations
     *
     * @deprecated 102.0.0
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function _saveWebsiteIds($product)
    {
        if ($product->hasWebsiteIds()) {
            if ($this->_storeManager->isSingleStoreMode()) {
                $id = $this->_storeManager->getDefaultStoreView()->getWebsiteId();
                $product->setWebsiteIds([$id]);
            }
            $websiteIds = $product->getWebsiteIds();
            $product->setIsChangedWebsites(false);
            $changed = $this->getProductWebsiteLink()->saveWebsiteIds($product, $websiteIds);

            if ($changed) {
                $product->setIsChangedWebsites(true);
            }
        }

        return $this;
    }

    /**
     * Save product category relations
     *
     * @param DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 102.0.0
     */
    protected function _saveCategories(DataObject $object)
    {
        return $this;
    }

    /**
     * Get collection of product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Category\Collection
     */
    public function getCategoryCollection($product)
    {
        /** @var Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->joinField(
            'product_id',
            'catalog_category_product',
            'product_id',
            'category_id = entity_id',
            null
        )->addFieldToFilter(
            'product_id',
            (int)$product->getEntityId()
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
        $entityId = (int)$object->getEntityId();
        if (!isset($this->availableCategoryIdsCache[$entityId])) {
            $unionTables = [];
            foreach ($this->_storeManager->getStores() as $store) {
                $unionTables[] = $this->getAvailableInCategoriesSelect(
                    $entityId,
                    $this->tableMaintainer->getMainTable($store->getId())
                );
            }
            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $unionTables,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );
            $this->availableCategoryIdsCache[$entityId] = array_unique($this->getConnection()->fetchCol($unionSelect));
        }
        return $this->availableCategoryIdsCache[$entityId];
    }

    /**
     * Returns DB select for available categories.
     *
     * @param int $entityId
     * @param string $tableName
     * @return \Magento\Framework\DB\Select
     */
    private function getAvailableInCategoriesSelect($entityId, $tableName)
    {
        return $this->getConnection()->select()->distinct()->from(
            $tableName,
            ['category_id']
        )->where(
            'product_id = ? AND is_parent = 1',
            $entityId
        )->where(
            'visibility != ?',
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
        );
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return \Magento\Eav\Model\Entity\Attribute\Source\Table::class;
    }

    /**
     * Check availability display product in category
     *
     * @param \Magento\Catalog\Model\Product|int $product
     * @param int $categoryId
     * @return string
     */
    public function canBeShowInCategory($product, $categoryId)
    {
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product->getEntityId();
            $storeId = $product->getStoreId();
        } else {
            $productId = $product;
            $storeId = $this->_storeManager->getStore()->getId();
        }

        $select = $this->getConnection()->select()->from(
            $this->tableMaintainer->getMainTable($storeId),
            'product_id'
        )->where(
            'product_id = ?',
            (int)$productId
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
                    $this->getLinkField() => new \Zend_Db_Expr($connection->quote($newId)),
                    'value'
                ]
            )->where(
                $this->getLinkField() . ' = ?',
                $oldId
            );

            $connection->query(
                $connection->insertFromSelect(
                    $select,
                    $tableName,
                    ['attribute_id', 'store_id', $this->getLinkField(), 'value'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
                )
            );
        }

        // set status as disabled
        $statusAttribute = $this->getAttribute('status');
        $statusAttributeId = $statusAttribute->getAttributeId();
        $statusAttributeTable = $statusAttribute->getBackend()->getTable();
        $updateCond[] = $connection->quoteInto($this->getLinkField() . ' = ?', $newId);
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
            $productIds,
            \Zend_Db::INT_TYPE
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
            $result[$this->getResultKey($row['sku'], $productSkuList)] = $row['entity_id'];
        }
        return $result;
    }

    /**
     * Return correct key for result array in getProductIdsBySku
     * Allows for different case sku to be passed in search array
     * with original cased sku to be passed back in result array
     *
     * @param string $sku
     * @param array $productSkuList
     * @return string
     */
    private function getResultKey(string $sku, array $productSkuList): string
    {
        return in_array(strtolower($sku), array_map('strtolower', $productSkuList)) ? $sku : '';
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
     * @inheritDoc
     *
     * @param ProductEntity|object $object
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

    /**
     * Reset firstly loaded attributes
     *
     * @param AbstractModel $object
     * @param integer $entityId
     * @param array|null $attributes
     * @return $this
     * @since 101.0.0
     */
    public function load($object, $entityId, $attributes = [])
    {
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row = $this->getConnection()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $this->loadAttributesForObject($attributes, $object);
        $this->getEntityManager()->load($object, $entityId);
        return $this;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 101.0.0
     */
    protected function evaluateDelete($object, $id, $connection)
    {
        $where = [$this->getLinkField() . '=?' => $id];
        $this->objectRelationProcessor->delete(
            $this->transactionManager,
            $connection,
            $this->getEntityTable(),
            $this->getConnection()->quoteInto(
                $this->getLinkField() . '=?',
                $id
            ),
            [$this->getLinkField() => $id]
        );

        $this->loadAllAttributes($object);
        foreach ($this->getAttributesByTable() as $table => $attributes) {
            $this->getConnection()->delete(
                $table,
                $where
            );
        }
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     * @since 101.0.0
     */
    public function save(AbstractModel $object)
    {
        $this->getEntityManager()->save($object);
        return $this;
    }

    /**
     * Retrieve entity manager.
     *
     * @return EntityManager
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = ObjectManager::getInstance()
                ->get(EntityManager::class);
        }
        return $this->entityManager;
    }

    /**
     * Retrieve ProductWebsiteLink instance.
     *
     * @deprecated 102.0.0
     * @return ProductWebsiteLink
     */
    private function getProductWebsiteLink()
    {
        return ObjectManager::getInstance()->get(ProductWebsiteLink::class);
    }

    /**
     * Retrieve CategoryLink instance.
     *
     * @deprecated 102.0.0
     * @return Product\CategoryLink
     */
    private function getProductCategoryLink()
    {
        if (null === $this->productCategoryLink) {
            $this->productCategoryLink = ObjectManager::getInstance()
                ->get(Product\CategoryLink::class);
        }
        return $this->productCategoryLink;
    }

    /**
     * Extends parent method to be appropriate for product.
     *
     * Store id is required to correctly identify attribute value we are working with.
     *
     * @inheritdoc
     * @since 102.0.0
     */
    protected function getAttributeRow($entity, $object, $attribute)
    {
        $data = parent::getAttributeRow($entity, $object, $attribute);
        $data['store_id'] = $object->getStoreId();
        return $data;
    }

    /**
     * After delete entity process
     *
     * @param DataObject $object
     * @return $this
     */
    protected function _afterDelete(DataObject $object)
    {
        $this->mediaImageDeleteProcessor->execute($object);
        return parent::_afterDelete($object);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->availableCategoryIdsCache = [];
        $this->_type = null;
        $this->_entityTable = null;
        $this->_entityIdField = null;
        $this->linkIdField = null;
    }
}
