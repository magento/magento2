<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 */
interface CustomJoinInterface
{
    /**
     * Make custom joins to collection
     *
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(AbstractDb $collection);
}
