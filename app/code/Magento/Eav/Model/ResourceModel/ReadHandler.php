<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Model\Operation\ContextHandlerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var AppResource
     */
    protected $appResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ContextHandlerInterface
     */
    protected $contextHandler;

    /**
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AppResource $appResource
     * @param ContextHandlerInterface $contextHandler
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AppResource $appResource,
        ContextHandlerInterface $contextHandler
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->appResource = $appResource;
        $this->contextHandler = $contextHandler;
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
        return $this->contextHandler->retrieve(
            $this->metadataPool->getMetadata($entityType),
            $data
        );
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
}
