<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

/**
 * Bulk operation item repository interface.
 *
 * An bulk is a group of queue messages. An bulk operation item is a queue message.
 * @api
 * @since 100.3.0
 */
interface OperationRepositoryInterface
{
    /**
     * Lists the bulk operation items that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface
     * @since 100.3.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
