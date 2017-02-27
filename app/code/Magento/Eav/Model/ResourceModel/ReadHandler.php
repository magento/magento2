<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel;

class ReadHandler implements \Magento\Framework\EntityManager\Operation\AttributeInterface
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\Model\Entity\ScopeResolver
     */
    protected $scopeResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /** @var \Magento\Eav\Model\Config */
    private $config;

    /**
     * ReadHandler constructor.
     *
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\Model\Entity\ScopeResolver $scopeResolver
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Eav\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\Model\Entity\ScopeResolver $scopeResolver,
        \Psr\Log\LoggerInterface $logger,
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
     */
    protected function getAttributes($entityType)
    {
        try {
            $metadata = $this->metadataPool->getMetadata($entityType);
        } catch (\Exception $e) {
            throw $e;
        }
        $eavEntityType = $metadata->getEavEntityType();
        $attributes = (null === $eavEntityType) ? [] : $this->config->getAttributes($eavEntityType);
        return $attributes;
    }

    /**
     * @param \Magento\Framework\Model\Entity\ScopeInterface $scope
     * @return array
     */
    protected function getContextVariables(\Magento\Framework\Model\Entity\ScopeInterface $scope)
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
