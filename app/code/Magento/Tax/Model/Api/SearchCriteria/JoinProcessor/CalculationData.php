<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Db\Select;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection as CalculationRuleCollection;

/**
 * Class CalculationData
 * @package Magento\Tax\Model\Api\SearchCriteria\JoinProcessor
 */
class CalculationData implements CustomJoinInterface
{
    /** Alias of table, that will be joined */
    const CALCULATION_DATA_ALIAS = "cd";

    /**
     * @param CalculationRuleCollection $collection
     * @return bool
     */
    public function apply(AbstractDb $collection)
    {
        $isNotApplied = !array_key_exists(
            self::CALCULATION_DATA_ALIAS,
            $collection->getSelect()->getPart(Select::FROM)
        );
        if ($isNotApplied) {
            $collection->joinCalculationData(self::CALCULATION_DATA_ALIAS);
            return true;
        }

        return false;
    }
}
