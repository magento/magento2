<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

/**
 * @api
 * @since 2.0.0
 */
interface WebsiteManagementInterface
{
    /**
     * Provide the number of website count
     *
     * @return int
     * @since 2.0.0
     */
    public function getCount();
}
