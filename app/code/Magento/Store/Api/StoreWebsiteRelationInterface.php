<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * @api
 */

interface StoreWebsiteRelationInterface
{
    /**
     * Get assigned to website store
     *
     * @param $websiteId
     * @return array
     */
    public function getStoreByWebsiteId($websiteId);
}
