<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * @api
 */
interface CustomerManagementInterface
{
    /**
     * Provide the number of customer count
     *
     * @return int
     */
    public function getCount();
}
