<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Based on Magento\Framework\Api\Filter builds condition
 * that can be applied to Catalog\Model\ResourceModel\Product\Collection
 * to filter products that has specific value for EAV attribute
 */
class EavAttributeCondition implements CustomConditionInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->eavConfig = $eavConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Build condition to filter product collection by EAV attribute
     *
     * @param Filter $filter
     * @return string
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(Filter $filter): string
    {
        $attribute = $this->getAttributeByCode($filter->getField());
        $tableAlias = 'ca_' . $attribute->getAttributeCode();
        $entityIdField = $attribute->getEntityIdField();
        $isNegative = $this->isNegativeConditionType($filter->getConditionType());

        $conditionType = $this->mapConditionType($filter->getConditionType());
        if ($isNegative) {
            $conditionType = $this->mapToPositiveConditionType($conditionType);
        }
        $conditionValue = $this->mapConditionValue($conditionType, $filter->getValue());

        // NOTE: store scope was ignored intentionally to perform search across all stores
        if ($conditionType === 'is_null') {
            $entityResourceModel = $attribute->getEntity();
            $attributeSelect = $this->resourceConnection->getConnection()
                ->select()
                ->from(
                    [Collection::MAIN_TABLE_ALIAS => $entityResourceModel->getEntityTable()],
                    Collection::MAIN_TABLE_ALIAS . '.' . $entityIdField
                )->joinLeft(
                    [$tableAlias => $attribute->getBackendTable()],
                    $tableAlias . '.' . $entityIdField . '=' . Collection::MAIN_TABLE_ALIAS .
                    '.' . $entityIdField . ' AND ' . $tableAlias . '.' .
                    $attribute->getIdFieldName() . '=' . $attribute->getAttributeId(),
                    ''
                )->where(
                    $tableAlias . '.value IS NULL OR ' . $tableAlias . '.value = ?',
                    ''
                );
        } elseif ($conditionType === 'notnull') {
            $attributeSelect = $this->resourceConnection->getConnection()
                ->select()
                ->from(
                    [$tableAlias => $attribute->getBackendTable()],
                    $tableAlias . '.' . $entityIdField
                )->where(
                    $this->resourceConnection->getConnection()->prepareSqlCondition(
                        $tableAlias . '.' . $attribute->getIdFieldName(),
                        ['eq' => $attribute->getAttributeId()]
                    )
                )->where($tableAlias . '.value IS NOT NULL')
                ->where($tableAlias . '.value != ?', '');
        } else {
            $attributeSelect = $this->resourceConnection->getConnection()
                ->select()
                ->from(
                    [$tableAlias => $attribute->getBackendTable()],
                    $tableAlias . '.' . $entityIdField
                )->where(
                    $this->resourceConnection->getConnection()->prepareSqlCondition(
                        $tableAlias . '.' . $attribute->getIdFieldName(),
                        ['eq' => $attribute->getAttributeId()]
                    )
                )->where(
                    $this->resourceConnection->getConnection()->prepareSqlCondition(
                        $tableAlias . '.value',
                        [$conditionType => $conditionValue]
                    )
                );
        }

        $outerConditionType = $isNegative ? 'nin' : 'in';

        return $this->resourceConnection
            ->getConnection()
            ->prepareSqlCondition(
                Collection::MAIN_TABLE_ALIAS . '.' . $entityIdField,
                [
                    $outerConditionType => $attributeSelect
                ]
            );
    }

    /**
     * Get attribute entity by its code
     *
     * @param string $field
     * @return Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode(string $field): Attribute
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }

    /**
     * Map equal and not equal conditions to in and not in
     *
     * @param string $conditionType
     * @return mixed
     */
    private function mapConditionType(string $conditionType): string
    {
        $conditionsMap = [
            'eq' => 'in',
            'neq' => 'nin'
        ];

        return isset($conditionsMap[$conditionType]) ? $conditionsMap[$conditionType] : $conditionType;
    }

    /**
     * Whether the filter condition type is a negative comparison.
     *
     * @param string $conditionType
     * @return bool
     */
    private function isNegativeConditionType(string $conditionType): bool
    {
        return in_array($conditionType, ['neq', 'nin', 'nlike'], true);
    }

    /**
     * Map a (possibly already-mapped) negative condition type to its positive counterpart.
     *
     * @param string $conditionType
     * @return string
     */
    private function mapToPositiveConditionType(string $conditionType): string
    {
        $conditionsMap = [
            'neq' => 'eq',
            'nin' => 'in',
            'nlike' => 'like',
        ];

        return $conditionsMap[$conditionType] ?? $conditionType;
    }

    /**
     * Wraps value with '%' if condition type is 'like' or 'not like'
     *
     * @param string $conditionType
     * @param string $conditionValue
     * @return string
     */
    private function mapConditionValue(string $conditionType, string $conditionValue): string
    {
        $conditionsMap = ['like', 'nlike'];

        if (in_array($conditionType, $conditionsMap, true)) {
            $conditionValue = '%' . $conditionValue . '%';
        }

        return $conditionValue;
    }
}
