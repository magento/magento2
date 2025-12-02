<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\Api;

/**
 * Interface StoreWebsiteRelationInterface
 * Provides stores information by website id
 * @package Magento\Store\Api
 * @api
 * @since 100.2.0
 */
interface StoreWebsiteRelationInterface
{
    /**
     * Get assigned to website store
     * @param int $websiteId
     * @return array
     * @since 100.2.0
     */
    public function getStoreByWebsiteId($websiteId);
}
