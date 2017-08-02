<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class ProductTaxClass
 * @package Magento\Tax\Model\Api\SearchCriteria\JoinProcessor
 * @since 2.2.0
 */
class ProductTaxClass implements CustomJoinInterface
{
    /**
     * @param \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection $collection
     * @return true
     * @since 2.2.0
     */
    public function apply(AbstractDb $collection)
    {
        $collection->joinCalculationData('ptc');
        return true;
    }
}
