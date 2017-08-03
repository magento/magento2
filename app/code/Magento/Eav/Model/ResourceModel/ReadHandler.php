<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;
use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Framework\Model\Entity\ScopeResolver;
use Psr\Log\LoggerInterface;

/**
 * Class \Magento\Eav\Model\ResourceModel\ReadHandler
 *
 * @since 2.1.0
 */
class ReadHandler implements AttributeInterface
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var ScopeResolver
     * @since 2.1.0
     */
    protected $scopeResolver;

    /**
     * @var LoggerInterface
     * @since 2.1.3
     */
    private $logger;

    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.2.0
     */
    private $config;

    /**
     * ReadHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ScopeResolver $scopeResolver
     * @param LoggerInterface $logger
     * @param \Magento\Eav\Model\Config $config
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        ScopeResolver $scopeResolver,
        LoggerInterface $logger,
        \Magento\Eav\Model\Config $config
    ) {
        $this->metadataPool = $metadataPool;
        $this->scopeResolver = $scopeResolver;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Get attribute of given entity type
     *
     * @param string $entityType
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception if for unknown entity type
     * @since 2.1.0
     */
    protected function getAttributes($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $eavEntityType = $metadata->getEavEntityType();
        $attributes = (null === $eavEntityType) ? [] : $this->config->getAttributes($eavEntityType);
        return $attributes;
    }

    /**
     * @param ScopeInterface $scope
     * @return array
     * @since 2.1.0
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
     * @since 2.1.0
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
                    $this->logger->warning(
                        "Attempt to load value of nonexistent EAV attribute '{$attributeValue['attribute_id']}' 
                        for entity type '$entityType'."
                    );
                }
            }
        }
        return $entityData;
    }
}
