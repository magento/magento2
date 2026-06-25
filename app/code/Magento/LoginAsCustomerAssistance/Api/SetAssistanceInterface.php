<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
