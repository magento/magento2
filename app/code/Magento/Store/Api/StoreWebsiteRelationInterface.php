<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * Interface StoreWebsiteRelationInterface
 * Provides stores information by website id
 * @package Magento\Store\Api
 * @api
 * @since 2.2.0
 */
interface StoreWebsiteRelationInterface
{
    /**
     * Get assigned to website store
     * @param int $websiteId
     * @return array
     * @since 2.2.0
     */
    public function getStoreByWebsiteId($websiteId);
}
