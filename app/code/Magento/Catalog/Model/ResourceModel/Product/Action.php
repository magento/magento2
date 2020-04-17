<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog Product Mass processing resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Action extends AbstractResource
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var TypeTransitionManager
     */
    private $typeTransitionManager;

    /**
     * Entity type id values to save
     *
     * @var array
     */
    private $typeIdValuesToSave = [];

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Factory $modelFactory
     * @param UniqueValidationInterface $uniqueValidator
     * @param DateTime $dateTime
     * @param CollectionFactory $productCollectionFactory
     * @param TypeTransitionManager $typeTransitionManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Factory $modelFactory,
        UniqueValidationInterface $uniqueValidator,
        DateTime $dateTime,
        ProductCollectionFactory $productCollectionFactory,
        TypeTransitionManager $typeTransitionManager,
        $data = []
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $data, $uniqueValidator);

        $this->dateTime = $dateTime;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->typeTransitionManager = $typeTransitionManager;
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType(
            Product::ENTITY
        )->setConnection(
            $resource->getConnection('catalog')
        );
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return $this
     * @throws \Exception
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        $object = new DataObject();
        $object->setStoreId($storeId);

        $attrData[ProductInterface::UPDATED_AT] = $this->dateTime->gmtDate();
        $this->getConnection()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                if ($attrCode === ProductAttributeInterface::CODE_HAS_WEIGHT) {
                    $this->updateHasWeightAttribute($entityIds, $value);
                    continue;
                }

                $attribute = $this->getAttribute($attrCode);
                if (!$attribute->getAttributeId()) {
                    continue;
                }

                $i = 0;
                foreach ($entityIds as $entityId) {
                    $i++;
                    $object->setId($entityId);
                    $object->setEntityId($entityId);
                    // collect data for save
                    $this->_saveAttributeValue($object, $attribute, $value);
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
                $this->_processAttributeValues();
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Insert or Update attribute data
     *
     * @param AbstractModel $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $connection = $this->getConnection();
        $storeId = (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        $table = $attribute->getBackend()->getTable();

        $entityId = $this->resolveEntityId($object->getId(), $table);

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if ($this->_storeManager->hasSingleStore() && !$attribute->isStatic()) {
            $storeId = $this->getDefaultStoreId();
            $connection->delete(
                $table,
                [
                    'attribute_id = ?' => $attribute->getAttributeId(),
                    $this->getLinkField() . ' = ?' => $entityId,
                    'store_id <> ?' => $storeId
                ]
            );
        }

        $data = $attribute->isStatic()
            ? new DataObject(
                [
                    $this->getLinkField() => $entityId,
                    $attribute->getAttributeCode() => $this->_prepareValueForSave($value, $attribute),
                ]
            )
            : new DataObject(
                [
                    'attribute_id' => $attribute->getAttributeId(),
                    'store_id' => $storeId,
                    $this->getLinkField() => $entityId,
                    'value' => $this->_prepareValueForSave($value, $attribute),
                ]
            );
        $bind = $this->_prepareDataForTable($data, $table);

        if ($attribute->isScopeStore() || $attribute->isStatic()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int) $storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

        return $this;
    }

    /**
     * Resolve entity id for current entity
     *
     * @param int $entityId
     *
     * @return int
     */
    protected function resolveEntityId($entityId)
    {
        if ($this->getIdFieldName() == $this->getLinkField()) {
            return $entityId;
        }
        $select = $this->getConnection()->select();
        $tableName = $this->_resource->getTableName('catalog_product_entity');
        $select->from($tableName, [$this->getLinkField()])
            ->where('entity_id = ?', $entityId);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Process product_has_weight attribute update
     *
     * @param array $entityIds
     * @param string $value
     */
    private function updateHasWeightAttribute($entityIds, $value): void
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($entityIds);
        // Type can be changed depending on weight only between simple and virtual products
        $productCollection->addFieldToFilter(
            Product::TYPE_ID,
            [
                'in' => [
                    Type::TYPE_SIMPLE,
                    Type::TYPE_VIRTUAL
                ]
            ]
        );
        $productCollection->addFieldToSelect(Product::TYPE_ID);
        $i = 0;

        foreach ($productCollection->getItems() as $product) {
            $product->setData(ProductAttributeInterface::CODE_HAS_WEIGHT, $value);
            $oldTypeId = $product->getTypeId();
            $this->typeTransitionManager->processProduct($product);

            if ($oldTypeId !== $product->getTypeId()) {
                $i++;
                $this->saveTypeIdValue($product);

                // save collected data every 1000 rows
                if ($i % 1000 === 0) {
                    $this->processTypeIdValues();
                }
            }
        }

        $this->processTypeIdValues();
    }

    /**
     * Save type id value to be updated
     *
     * @param Product $product
     * @return $this
     */
    private function saveTypeIdValue($product): self
    {
        $typeId = $product->getTypeId();

        if (!array_key_exists($typeId, $this->typeIdValuesToSave)) {
            $this->typeIdValuesToSave[$typeId] = [];
        }

        $this->typeIdValuesToSave[$typeId][] = $product->getId();

        return $this;
    }

    /**
     * Update type id values
     *
     * @return $this
     */
    private function processTypeIdValues(): self
    {
        $connection = $this->getConnection();
        $table = $this->getTable('catalog_product_entity');

        foreach ($this->typeIdValuesToSave as $typeId => $entityIds) {
            $connection->update(
                $table,
                ['type_id' => $typeId],
                ['entity_id IN (?)' => $entityIds]
            );
        }
        $this->typeIdValuesToSave = [];

        return $this;
    }
}
