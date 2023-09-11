<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Api;

/**
 * Set 'assistance_allowed' attribute to Customer.
 *
 * @api
 */
interface SetAssistanceInterface
{
    /**
     * Set 'assistance_allowed' attribute to Customer by id.
     *
     * @param int $customerId
     * @param bool $isEnabled
     */
    public function execute(int $customerId, bool $isEnabled): void;
}
