<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * @api
 * @since 2.0.0
 */
interface CustomerManagementInterface
{
    /**
     * Provide the number of customer count
     *
     * @return int
     * @since 2.0.0
     */
    public function getCount();
}
