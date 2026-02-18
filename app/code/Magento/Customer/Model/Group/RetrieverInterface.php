<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Group;

/**
 * Interface for getting current customer group from session.
 *
 * @api
 * @since 101.0.0
 */
interface RetrieverInterface
{
    /**
     * Retrieve customer group id.
     *
     * @return int
     * @since 101.0.0
     */
    public function getCustomerGroupId();
}
