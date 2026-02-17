<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model;

use Exception;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class GetCustomerGroupName
{
    /**
     * GetCustomerGroupName Constructor
     *
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * Get customer group name using customer group id
     *
     * @param int $groupId
     * @param int $websiteId
     * @return array
     * @throws GraphQlInputException
     */
    public function execute(int $groupId, int $websiteId): array
    {
        try {
            $customerGroup = $this->groupRepository->getById($groupId);
            $excludedWebsiteIds = $customerGroup->getExtensionAttributes()->getExcludeWebsiteIds() ?? [];
        } catch (Exception $e) {
            throw new GraphQlInputException(__('The specified customer group is invalid or does not exist.'));
        }

        return in_array($websiteId, $excludedWebsiteIds) ? [] : ['name' => $customerGroup->getCode()];
    }
}
