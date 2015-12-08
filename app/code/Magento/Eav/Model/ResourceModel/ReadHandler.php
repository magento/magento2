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

/**
 * Class ReadHandler
 */
class ReadHandler
{
    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        // TODO: Implement context fallback handling
        foreach ($contextFields as $field) {
            if (isset($data[$field])) {
                $context[$field] = [$data[$field], '0'];
            }
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
    public function execute($entityType, $entityData)
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
}
