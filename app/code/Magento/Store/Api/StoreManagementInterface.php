<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * @api
 * @since 100.0.2
 */
interface StoreManagementInterface
{
    /**
     * Provide the number of store count
     *
     * @return int
     */
    public function getCount();
}
