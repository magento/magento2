<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class RateCode
 * @package Magento\Tax\Model\Api\SearchCriteria\JoinProcessor
 */
class RateCode implements CustomJoinInterface
{
    /**
     * @param AbstractDb $collection
     * @return true
     */
    public function apply(AbstractDb $collection)
    {
        $taxCalculationTableAlias = 'tc';

        $collection->joinCalculationData($taxCalculationTableAlias);

        $collection->getSelect()->joinLeft(
            ['rc' => $collection->getTable('tax_calculation_rate')],
            "{$taxCalculationTableAlias}.tax_calculation_rate_id = rc.tax_calculation_rate_id",
            []
        );

        return true;
    }
}
