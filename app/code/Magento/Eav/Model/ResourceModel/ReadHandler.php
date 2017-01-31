<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Model\Entity\ScopeResolver;
use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Framework\EntityManager\Operation\AttributeInterface;
use Magento\Eav\Model\Entity\AttributeCache;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReadHandler implements AttributeInterface
{
    /**
     * @var AttributeCache
     */
    private $attributeCache;

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
     * @var ScopeResolver
     */
    protected $scopeResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReadHandler constructor.
     *
     * @param AttributeRepository $attributeRepository
     * @param MetadataPool $metadataPool
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AppResource $appResource
     * @param ScopeResolver $scopeResolver
     * @param AttributeCache $attributeCache
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        MetadataPool $metadataPool,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AppResource $appResource,
        ScopeResolver $scopeResolver,
        AttributeCache $attributeCache
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->appResource = $appResource;
        $this->scopeResolver = $scopeResolver;
        $this->attributeCache = $attributeCache;
    }

    /**
     * Get Logger
     *
     * @return LoggerInterface
     * @deprecated
     */
    private function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()->create(LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception
     */
    protected function getAttributes($entityType)
    {
        $attributes = $this->attributeCache->getAttributes($entityType);
        if ($attributes) {
            return $attributes;
        }
        $metadata = $this->metadataPool->getMetadata($entityType);
        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->create()
        );
        $attributes = $searchResult->getItems();
        $this->attributeCache->saveAttributes($entityType, $attributes);
        return $attributes;
    }

    /**
     * @param ScopeInterface $scope
     * @return array
     */
    protected function getContextVariables(ScopeInterface $scope)
    {
        $data[] = $scope->getValue();
        if ($scope->getFallback()) {
            $data = array_merge($data, $this->getContextVariables($scope->getFallback()));
        }
        return $data;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\ConfigurationMismatchException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        if (!$metadata->getEavEntityType()) {//todo hasCustomAttributes
            return $entityData;
        }
        $context = $this->scopeResolver->getEntityContext($entityType, $entityData);
        $connection = $metadata->getEntityConnection();

        $attributeTables = [];
        $attributesMap = [];
        $selects = [];

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($this->getAttributes($entityType) as $attribute) {
            if (!$attribute->isStatic()) {
                $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
                $attributesMap[$attribute->getAttributeId()] = $attribute->getAttributeCode();
            }
        }
        if (count($attributeTables)) {
            $attributeTables = array_keys($attributeTables);
            foreach ($attributeTables as $attributeTable) {
                $select = $connection->select()
                    ->from(
                        ['t' => $attributeTable],
                        ['value' => 't.value', 'attribute_id' => 't.attribute_id']
                    )
                    ->where($metadata->getLinkField() . ' = ?', $entityData[$metadata->getLinkField()]);
                foreach ($context as $scope) {
                    //TODO: if (in table exists context field)
                    $select->where(
                        $metadata->getEntityConnection()->quoteIdentifier($scope->getIdentifier()) . ' IN (?)',
                        $this->getContextVariables($scope)
                    )->order('t.' . $scope->getIdentifier() . ' DESC');
                }
                $selects[] = $select;
            }
            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $selects,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );
            foreach ($connection->fetchAll($unionSelect) as $attributeValue) {
                if (isset($attributesMap[$attributeValue['attribute_id']])) {
                    $entityData[$attributesMap[$attributeValue['attribute_id']]] = $attributeValue['value'];
                } else {
                    $this->getLogger()->warning(
                        "Attempt to load value of nonexistent EAV attribute '{$attributeValue['attribute_id']}'
                        for entity type '$entityType'."
                    );
                }
            }
        }
        return $entityData;
    }
}
