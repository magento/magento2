<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * This filter is using indexers for prices and will not work without indexers
 */
class ProductPriceFilter implements CustomFilterInterface
{
    /**
     * Apply prices Filter to Product Collection
     *
     * @param Filter $filter
     * @param Collection $collection
     * @return bool Whether the filter is applied
     */
    public function apply(Filter $filter, AbstractDb $collection) : bool
    {
        $collection->addFinalPrice();
        $collection->addMinimalPrice();
        $collection->addPriceData();
        $collection->addTaxPercents();

        $conditionType = $filter->getConditionType();
        $sqlCondition = $collection
            ->getConnection()
            ->prepareSqlCondition(
                Collection::INDEX_TABLE_ALIAS . '.' . $filter->getField(),
                [$conditionType => $filter->getValue()]
            );
        $collection->getSelect()->where($sqlCondition);
        return true;
    }
}
