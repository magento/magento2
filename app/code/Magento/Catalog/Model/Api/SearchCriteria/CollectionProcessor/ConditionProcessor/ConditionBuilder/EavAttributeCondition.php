<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class EavAttributeCondition
 * Based on Magento\Framework\Api\Filter builds condition
 * that can be applied to Catalog\Model\ResourceModel\Product\Collection
 * to filter products that has specific value for EAV attribute
 *
 * @package Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder
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
     * @param Filter $filter
     * @return string
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(Filter $filter)
    {
        $attribute = $this->getAttributeByCode($filter->getField());
        $tableAlias = 'ca_' . $attribute->getAttributeCode();

        $conditionType = $this->mapConditionType($filter->getConditionType());
        $conditionValue = $this->mapConditionValue($conditionType, $filter->getValue());

        // NOTE: store scope was ignored intentionally to perform search across all stores
        $attributeSelect = $this->resourceConnection->getConnection()
            ->select()
            ->from(
                [$tableAlias => $attribute->getBackendTable()],
                $tableAlias . '.' . $attribute->getEntityIdField()
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

        return $this->resourceConnection
            ->getConnection()
            ->prepareSqlCondition(
                Collection::MAIN_TABLE_ALIAS . '.' . $attribute->getEntityIdField(),
                [
                    'in' => $attributeSelect
                ]
            );
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }

    /**
     * Map equal and not equal conditions to in and not in
     *
     * @param string $conditionType
     * @return mixed
     */
    private function mapConditionType($conditionType)
    {
        $conditionsMap = [
            'eq' => 'in',
            'neq' => 'nin'
        ];

        return isset($conditionsMap[$conditionType]) ? $conditionsMap[$conditionType] : $conditionType;
    }

    /**
     * Wraps value with '%' if condition type is 'like' or 'not like'
     *
     * @param $conditionType
     * @param $conditionValue
     * @return string
     */
    private function mapConditionValue($conditionType, $conditionValue)
    {
        $conditionsMap = ['like', 'nlike'];

        if (in_array($conditionType, $conditionsMap)) {
            $conditionValue = '%' . $conditionValue . '%';
        }

        return $conditionValue;
    }
}
