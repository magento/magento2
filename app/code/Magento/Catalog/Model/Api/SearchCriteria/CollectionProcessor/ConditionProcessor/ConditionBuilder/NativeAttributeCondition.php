<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\ConditionBuilder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Based on Magento\Framework\Api\Filter builds condition
 * that can be applied to Catalog\Model\ResourceModel\Product\Collection
 * to filter products that has specific value for their native attribute
 */
class NativeAttributeCondition implements CustomConditionInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Filter $filter
     * @return string
     * @throws \DomainException
     */
    public function build(Filter $filter): string
    {
        $conditionType = $this->mapConditionType($filter->getConditionType(), $filter->getField());
        $conditionValue = $this->mapConditionValue($conditionType, $filter->getValue());

        return $this->resourceConnection
            ->getConnection()
            ->prepareSqlCondition(
                Collection::MAIN_TABLE_ALIAS . '.' . $filter->getField(),
                [
                    $conditionType => $conditionValue
                ]
            );
    }

    /**
     * Map equal and not equal conditions to in and not in
     *
     * @param string $conditionType
     * @param string $field
     * @return mixed
     */
    private function mapConditionType(string $conditionType, string $field): string
    {
        if (strtolower($field) === ProductInterface::SKU) {
            $conditionsMap = [
                'eq' => 'like',
                'neq' => 'nlike'
            ];
        } else {
            $conditionsMap = [
                'eq' => 'in',
                'neq' => 'nin'
            ];
        }

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

        if (in_array($conditionType, $conditionsMap)) {
            $conditionValue = '%' . $conditionValue . '%';
        }

        return $conditionValue;
    }
}
