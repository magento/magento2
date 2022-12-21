<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\GroupExcludedWebsiteInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Customer group website repository interface for websites that are excluded from customer group.
 * @api
 */
interface GroupExcludedWebsiteRepositoryInterface
{
    /**
     * Save customer group excluded website.
     *
     * @param GroupExcludedWebsiteInterface $groupExcludedWebsite
     * @return GroupExcludedWebsiteInterface
     * @throws LocalizedException
     */
    public function save(GroupExcludedWebsiteInterface $groupExcludedWebsite): GroupExcludedWebsiteInterface;

    /**
     * Retrieve customer group excluded websites by customer group id.
     *
     * @param int $customerGroupId
     * @return string[]
     * @throws LocalizedException
     */
    public function getCustomerGroupExcludedWebsites(int $customerGroupId): array;

    /**
     * Retrieve all excluded customer group websites per customer groups.
     *
     * @return int[]
     * @throws LocalizedException
     */
    public function getAllExcludedWebsites(): array;

    /**
     * Delete customer group with its excluded websites.
     *
     * @param int $customerGroupId
     * @return bool
     * @throws LocalizedException
     */
    public function delete(int $customerGroupId): bool;

    /**
     * Delete customer group excluded website by id.
     *
     * @param int $websiteId
     * @return bool
     * @throws LocalizedException
     */
    public function deleteByWebsite(int $websiteId): bool;
}
