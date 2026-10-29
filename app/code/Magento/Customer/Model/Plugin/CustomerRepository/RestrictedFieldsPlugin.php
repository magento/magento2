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
                if ($this->isRestricted($filter->getField())) {
                    throw new InputException(
                        __('The search criteria filter field is invalid.')
                    );
                }
            }
        }
        foreach ($searchCriteria->getSortOrders() ?? [] as $sortOrder) {
            if ($this->isRestricted($sortOrder->getField())) {
                throw new InputException(
                    __('The search criteria sort field is invalid.')
                );
            }
        }
        return [$searchCriteria];
    }

    /**
     * Check if the given field name matches a restricted field
     *
     * @param string|null $field
     * @return bool
     */
    private function isRestricted(?string $field): bool
    {
        return $field !== null && in_array(strtolower($field), $this->restrictedFields, true);
    }
}
