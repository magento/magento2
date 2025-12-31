<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\SearchCriteria;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 * @since 101.0.0
 */
interface CollectionProcessorInterface
{
    /**
     * Apply Search Criteria to Collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractDb $collection
     * @throws \InvalidArgumentException
     * @return void
     * @since 101.0.0
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection);
}
