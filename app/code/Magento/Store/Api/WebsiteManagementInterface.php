<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * @api
 */
interface WebsiteManagementInterface
{
    /**
     * Provide the number of website count
     *
     * @return int
     */
    public function getCount();
}
