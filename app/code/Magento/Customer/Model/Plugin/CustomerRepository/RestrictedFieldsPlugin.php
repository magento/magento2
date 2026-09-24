<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin\CustomerRepository;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * Forbidden customer fields
 */
class RestrictedFieldsPlugin
{
    /**
     * @param array $restrictedFields
     */
    public function __construct(
        private array $restrictedFields = []
    ) {
    }

    /**
     * Check forbidden customer fields
     *
     * @param CustomerRepositoryInterface $subject
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetList(
        CustomerRepositoryInterface $subject,
        SearchCriteriaInterface $searchCriteria
    ): array {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (in_array(strtolower($filter->getField()), $this->restrictedFields, true)) {
                    throw new InputException(
                        __('The search criteria filter field is invalid.')
                    );
                }
            }
        }
        foreach ($searchCriteria->getSortOrders() ?? [] as $sortOrder) {
            if (in_array(strtolower($sortOrder->getField()), $this->restrictedFields, true)) {
                throw new InputException(
                    __('The search criteria sort field is invalid.')
                );
            }
        }
        return [$searchCriteria];
    }
}
