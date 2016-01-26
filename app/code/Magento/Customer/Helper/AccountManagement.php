<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

/**
 * Customer helper for account management.
 */
class AccountManagement extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check if customer is locked
     * @param string $lockExpires
     * @return bool
     */
    public function isCustomerLocked($lockExpires)
    {
        if ($lockExpires) {
            $lockExpires = new \DateTime($lockExpires);
            if ($lockExpires > new \DateTime()) {
                return true;
            }
        }
        return false;
    }
}
