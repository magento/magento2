<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Group;

/**
 * Interface for getting current customer group from session.
 *
 * @api
 */
interface RetrieverInterface
{
    /**
     * Retrieve customer group id.
     *
     * @return int
     */
    public function getCustomerGroupId();
}
