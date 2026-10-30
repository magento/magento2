<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Api;

/**
 * @api
 * @since 100.0.2
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
