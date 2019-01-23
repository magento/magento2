<?php

declare(strict_types=1);

namespace Chizhov\Status\Api;

use Chizhov\Status\Api\Data\CustomerStatusInterface;
use Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/** @api */
interface CustomerStatusRepositoryInterface
{
    /**
     * Save customer status.
     *
     * @param \Chizhov\Status\Api\Data\CustomerStatusInterface $status
     * @return int
     * @throws \Magento\Framework\Validation\ValidationException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(CustomerStatusInterface $status): int;

    /**
     * Get customer status by given customer ID.
     *
     * @param int $customerId
     * @return \Chizhov\Status\Api\Data\CustomerStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $customerId): CustomerStatusInterface;

    /**
     * Find customer statuses by given SearchCriteria
     * SearchCriteria is not required because load all stocks is useful case.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): CustomerStatusSearchResultsInterface;

    /**
     * Delete the customer status data by customer ID.
     *
     * @param int $customerId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $customerId): void;
}
