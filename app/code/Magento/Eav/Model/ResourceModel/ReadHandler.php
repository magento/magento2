<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\App\ResourceConnection as AppResource;

/**
 * Class ReadHandler
 */
class ReadHandler
{
    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var AppResource
     */
    protected $appResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManager $storeManager
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManager $storeManager,
        AppResource $appResource
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->appResource = $appResource;
    }

    /**
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception
     */
    protected function getAttributes($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->create()
        );
        return $searchResult->getItems();
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return array
     */
    protected function getActionContext($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $contextFields = $metadata->getEntityContext();
        $context = [];
        if (isset($contextFields[\Magento\Store\Model\Store::STORE_ID])) {
            $context[\Magento\Store\Model\Store::STORE_ID] = $this->addStoreIdContext(
                $data,
                \Magento\Store\Model\Store::STORE_ID
            );
        }
        return $context;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeOldGoodImplementation($entityType, $entityData)
    {
        $data = [];
        $metadata = $this->metadataPool->getMetadata($entityType);
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        if ($metadata->getEavEntityType()) {
            foreach ($this->getAttributes($entityType) as $attribute) {
                if (!$attribute->isStatic()) {
                    $select = $metadata->getEntityConnection()->select()
                        ->from($attribute->getBackend()->getTable(), ['value'])
                        ->where($metadata->getLinkField() . ' = ?', $entityData[$metadata->getLinkField()])
                        ->where('attribute_id = ?', $attribute->getAttributeId());
                    $context = $this->getActionContext($entityType, $entityData);
                    foreach ($context as $field => $value) {
                        //TODO: if (in table exists context field)
                        $select->where(
                            $metadata->getEntityConnection()->quoteIdentifier($field) . ' IN (?)',
                            $value
                        )->order($field . ' DESC');
                    }
                    $value = $metadata->getEntityConnection()->fetchOne($select);
                    if ($value !== false) {
                        $data[$attribute->getAttributeCode()] = $value;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($entityType, $entityData)
    {
        $data = [];
        $metadata = $this->metadataPool->getMetadata($entityType);
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attributeTables = [];
        if ($metadata->getEavEntityType()) {
            $context = $this->getActionContext($entityType, $entityData);
            $context = $this->getActionContext($entityType, $entityData);
            foreach ($this->getAttributes($entityType) as $attribute) {
                if (!$attribute->isStatic()) {
                    $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
                }
            }
            $selects = [];
            foreach ($attributeTables as $attributeTable => $attributeCodes) {
                $select = $metadata->getEntityConnection()->select()
                    ->from(['t' => $attributeTable], ['value' => 't.value'])
                    ->join(
                        ['a' => $this->appResource->getTableName('eav_attribute')],
                        'a.attribute_id = t.attribute_id',
                        ['attribute_code' => 'a.attribute_code']
                    )
                    ->where($metadata->getLinkField() . ' = ?', $entityData[$metadata->getLinkField()])
                    ->where('t.attribute_id IN (?)', $attributeCodes)
                    ->order('a.attribute_id');
                foreach ($context as $field => $value) {
                    //TODO: if (in table exists context field)
                    $select->where(
                        $metadata->getEntityConnection()->quoteIdentifier($field) . ' IN (?)',
                        $value
                    )->order('t.' . $field . ' DESC');
                }
                $selects[] = $select;
            }

            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $selects,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );
            $attributeValues = $metadata->getEntityConnection()->fetchAll((string)$unionSelect);
            foreach ($attributeValues as $attributeValue) {
                $data[$attributeValue['attribute_code']] = $attributeValue['value'];
            }
        }
        return $data;
    }

    /**
     * Add store_id filter to context from object data or store manager
     *
     * @param array $data
     * @param string $field
     * @return array
     */
    protected function addStoreIdContext(array $data, $field)
    {
        if (isset($data[$field])) {
            $storeId = $data[$field];
        } else {
            $storeId = (int)$this->storeManager->getStore(true)->getId();
        }
        $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($storeId != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $storeIds[] = $storeId;
        }

        return $storeIds;
    }
}
