<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class UpdateHandler
 */
class UpdateHandler
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
     * @var AttributePersistor
     */
    protected $attributePersistor;

    /**
     * @var ReadSnapshot
     */
    protected $readSnapshot;

    /**
     * UpdateHandler constructor.
     *
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributePersistor $attributePersistor
     * @param ReadSnapshot $readSnapshot
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor,
        ReadSnapshot $readSnapshot
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributePersistor = $attributePersistor;
        $this->readSnapshot = $readSnapshot;
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
        foreach ($contextFields as $field) {
            if ('store_id' == $field && array_key_exists($field, $data) && $data[$field] == 1) {
                $context[$field] = 0;
                continue;
            }
            if (isset($data[$field])) {
                $context[$field] = $data[$field];
            }
        }
        return $context;
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute($entityType, $data)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $context = $this->getActionContext($entityType, $data);
            $snapshot = $this->readSnapshot->execute($entityType, $data);
            $processed = [];
            foreach ($this->getAttributes($entityType) as $attribute) {
                if ($attribute->isStatic()) {
                    continue;
                }
                if (isset($snapshot[$attribute->getAttributeCode()])
                    && $snapshot[$attribute->getAttributeCode()] !== false
                    && (array_key_exists($attribute->getAttributeCode(), $data)
                        && $attribute->isValueEmpty($data[$attribute->getAttributeCode()]))
                ) {
                    $this->attributePersistor->registerDelete(
                        $entityType,
                        $data[$metadata->getLinkField()],
                        $attribute->getAttributeCode()
                    );
                }
                if ((!array_key_exists($attribute->getAttributeCode(), $snapshot)
                    || $snapshot[$attribute->getAttributeCode()] === false)
                    && isset($data[$attribute->getAttributeCode()])
                    && !$attribute->isValueEmpty($data[$attribute->getAttributeCode()])
                ) {
                    $this->attributePersistor->registerInsert(
                        $entityType,
                        $data[$metadata->getLinkField()],
                        $attribute->getAttributeCode(),
                        $data[$attribute->getAttributeCode()]
                    );
                    $processed[$attribute->getAttributeCode()] = $data[$attribute->getAttributeCode()];
                }
                if (array_key_exists($attribute->getAttributeCode(), $snapshot)
                    && $snapshot[$attribute->getAttributeCode()] !== false
                    && isset($data[$attribute->getAttributeCode()])
                    && $snapshot[$attribute->getAttributeCode()] != $data[$attribute->getAttributeCode()]
                    && !$attribute->isValueEmpty($data[$attribute->getAttributeCode()])
                ) {
                    $this->attributePersistor->registerUpdate(
                        $entityType,
                        $data[$metadata->getLinkField()],
                        $attribute->getAttributeCode(),
                        $data[$attribute->getAttributeCode()]
                    );
                    $processed[$attribute->getAttributeCode()] = $data[$attribute->getAttributeCode()];
                }
            }
            $this->attributePersistor->flush($entityType, $context);
        }
        return $data;
    }
}
