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
 * Class CreateHandler
 */
class CreateHandler
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
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributePersistor $attributePersistor
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributePersistor $attributePersistor
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributePersistor = $attributePersistor;
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
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', null, 'neq')->create()
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
            if (isset($data[$field])) {
                $data[$field] = 0;
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
     */
    public function execute($entityType, $data)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */

        $metadata = $this->metadataPool->getMetadata($entityType);
        if ($metadata->getEavEntityType()) {
            $context = $this->getActionContext($entityType, $data);
            $processed = [];
            foreach ($this->getAttributes($entityType) as $attribute) {
                if ($attribute->isStatic()) {
                    continue;
                }
                if (isset($data[$attribute->getAttributeCode()])
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
            }
            $this->attributePersistor->flush($entityType, $context);
        }
        return $data;
    }
}
