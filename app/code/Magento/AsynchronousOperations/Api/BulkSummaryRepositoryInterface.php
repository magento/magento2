<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Api\SearchResults;
/**
 * Interface BulkSummaryRepositoryInterface
 */
interface BulkSummaryRepositoryInterface
{
    /**
     * Save Entity
     *
     * @param BulkSummaryInterface $entity
     *
     * @return void
     */
    public function saveBulk(BulkSummaryInterface $entity): void;

    /**
     * Save Operations for Bulk
     *
     * @param OperationInterface[] $operations
     * @return void
     */
    public function saveOperations(array $operations): void;

    /**
     * Get List of operations based on search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getOperationsList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * Delete list of operations by Ids
     *
     * @param array $operationIds
     * @return void
     */
    public function deleteOperationsById(array $operationIds): void;

    /**
     * Get Bulk by UUID
     *
     * @param string $bulkUuid
     * @return BulkSummaryInterface
     */
    public function getBulkByUuid(string $bulkUuid): BulkSummaryInterface;

}
