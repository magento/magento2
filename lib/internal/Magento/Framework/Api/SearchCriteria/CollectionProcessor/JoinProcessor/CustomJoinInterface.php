<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 * @since 101.0.0
 */
interface CustomJoinInterface
{
    /**
     * Make custom joins to collection
     *
     * @param AbstractDb $collection
     * @return bool
     * @since 101.0.0
     */
    public function apply(AbstractDb $collection);
}
