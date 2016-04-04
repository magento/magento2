<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * @api
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
