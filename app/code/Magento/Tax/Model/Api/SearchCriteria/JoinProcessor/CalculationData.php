<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class CalculationData implements CustomJoinInterface
{
    /** Alias of table, that will be joined */
    public const CALCULATION_DATA_ALIAS = "cd";

    /**
     * Apply join to collection
     *
     * @param \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection $collection
     * @return bool
     */
    public function apply(AbstractDb $collection)
    {
        $isNotApplied = !array_key_exists(
            self::CALCULATION_DATA_ALIAS,
            $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM)
        );
        if ($isNotApplied) {
            $collection->joinCalculationData(self::CALCULATION_DATA_ALIAS);
            return true;
        }

        return false;
    }
}
