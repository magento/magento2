<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Cache;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class GroupExcludedWebsiteCache implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private array $customerGroupExcludedWebsite = [];

    /**
     * Adds entry to GroupExcludedWebsite cache
     *
     * @param int $customerGroupId
     * @param array $value
     */
    public function addToCache(int $customerGroupId, array $value)
    {
        $this->customerGroupExcludedWebsite[$customerGroupId] = $value;
    }

    /**
     * Gets entry from GroupExcludedWebsite cache
     *
     * @param int $customerGroupId
     * @return array
     */
    public function getFromCache(int $customerGroupId): array
    {
        return $this->customerGroupExcludedWebsite[$customerGroupId] ?? [];
    }

    /**
     * Checks presence of cached customer group in GroupExcludedWebsite cache
     *
     * @param int $customerGroupId
     * @return bool
     */
    public function isCached(int $customerGroupId): bool
    {
        return isset($this->customerGroupExcludedWebsite[$customerGroupId]);
    }

    /**
     * Cleans the cache
     */
    public function invalidate()
    {
        $this->customerGroupExcludedWebsite = [];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->invalidate();
    }
}
