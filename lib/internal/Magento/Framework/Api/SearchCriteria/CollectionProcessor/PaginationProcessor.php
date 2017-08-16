<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class \Magento\Framework\Api\SearchCriteria\CollectionProcessor\PaginationProcessor
 *
 */
class PaginationProcessor implements CollectionProcessorInterface
{
    /**
     * Apply Search Criteria Pagination to collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @return void
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
    }
}
