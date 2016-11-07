<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class CalculationData
 * @package Magento\Tax\Model\Api\SearchCriteria\JoinProcessor
 */
class CalculationData implements CustomJoinInterface
{
    /** Alias of table, that will be joined */
    const CALCULATION_DATA_ALIAS = "cd";

    /**
     * @param \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection $collection
     * @return bool
     */
    public function apply(AbstractDb $collection)
    {
        $isNotApplied = !array_key_exists(
            self::CALCULATION_DATA_ALIAS,
            $collection->getSelect()->getPart(\Magento\Framework\Db\Select::FROM)
        );
        if ($isNotApplied) {
            $collection->joinCalculationData(self::CALCULATION_DATA_ALIAS);
            return true;
        }

        return false;
    }
}
