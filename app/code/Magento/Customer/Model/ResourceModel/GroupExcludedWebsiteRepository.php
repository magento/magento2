<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

use Magento\Customer\Api\Data\GroupExcludedWebsiteInterface;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Customer group website repository for CRUD operations with excluded websites.
 */
class GroupExcludedWebsiteRepository implements GroupExcludedWebsiteRepositoryInterface
{
    /**
     * @var GroupExcludedWebsite
     */
    private $groupExcludedWebsiteResourceModel;

    /**
     * @param GroupExcludedWebsite $groupExcludedWebsiteResourceModel
     */
    public function __construct(
        GroupExcludedWebsite $groupExcludedWebsiteResourceModel
    ) {
        $this->groupExcludedWebsiteResourceModel = $groupExcludedWebsiteResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function save(GroupExcludedWebsiteInterface $groupExcludedWebsite): GroupExcludedWebsiteInterface
    {
        try {
            $this->groupExcludedWebsiteResourceModel->save($groupExcludedWebsite);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save customer group website to exclude from customer group: "%1"', $e->getMessage())
            );
        }

        return $groupExcludedWebsite;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupExcludedWebsites(int $customerGroupId): array
    {
        try {
            return $this->groupExcludedWebsiteResourceModel->loadCustomerGroupExcludedWebsites($customerGroupId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not retrieve excluded customer group websites by customer group: "%1"', $e->getMessage())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getAllExcludedWebsites(): array
    {
        try {
            $allExcludedWebsites = $this->groupExcludedWebsiteResourceModel->loadAllExcludedWebsites();
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not retrieve all excluded customer group websites.')
            );
        }

        $excludedWebsites = [];

        if (!empty($allExcludedWebsites)) {
            foreach ($allExcludedWebsites as $allExcludedWebsite) {
                $customerGroupId = (int)$allExcludedWebsite['customer_group_id'];
                $websiteId = (int)$allExcludedWebsite['website_id'];
                $excludedWebsites[$customerGroupId][] = $websiteId;
            }
        }

        return $excludedWebsites;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $customerGroupId): bool
    {
        try {
            $this->groupExcludedWebsiteResourceModel->delete($customerGroupId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not delete customer group with its excluded websites.')
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByWebsite(int $websiteId): bool
    {
        try {
            return (bool)$this->groupExcludedWebsiteResourceModel->deleteByWebsite($websiteId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not delete customer group excluded website by id.')
            );
        }
    }
}
