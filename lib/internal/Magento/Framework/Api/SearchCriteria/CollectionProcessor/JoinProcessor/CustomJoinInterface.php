<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\JoinProcessor;

use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @api
 * @since 100.2.0
 */
interface CustomJoinInterface
{
    /**
     * Make custom joins to collection
     *
     * @param AbstractDb $collection
     * @return bool
     * @since 100.2.0
     */
    public function apply(AbstractDb $collection);
}
