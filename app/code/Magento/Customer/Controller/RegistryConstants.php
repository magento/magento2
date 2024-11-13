<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller;

/**
 * Declarations of core registry keys used by the Customer module
 *
 * @api
 */
class RegistryConstants
{
    /**
     * Registry key where current customer ID is stored
     */
    const CURRENT_CUSTOMER_ID = 'current_customer_id';

    /**
     * Registry key where current CustomerGroup ID is stored
     */
    const CURRENT_GROUP_ID = 'current_group_id';
}
