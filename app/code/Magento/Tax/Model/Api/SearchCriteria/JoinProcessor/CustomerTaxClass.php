<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Api\SearchCriteria\JoinProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor\CustomJoinInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class CustomerTaxClass
 * @package Magento\Tax\Model\Api\SearchCriteria\JoinProcessor
 */
class CustomerTaxClass implements CustomJoinInterface
{
    /**
     * @param \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection $collection
     * @return true
     */
    public function apply(AbstractDb $collection)
    {
        $collection->joinCalculationData('ctc');
        return true;
    }
}
