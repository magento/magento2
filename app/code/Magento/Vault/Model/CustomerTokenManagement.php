<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Customer\Model\Session;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class CustomerTokenManagement
{
    /**
     * CustomerTokenManagement constructor.
     * @param PaymentTokenManagement $tokenManagement
     * @param Session $session
     */
    public function __construct(
        private readonly PaymentTokenManagement $tokenManagement,
        private readonly Session $session
    ) {
    }

    /**
     * Returns list of payment tokens for current customer session
     *
     * @return PaymentTokenInterface[]
     */
    public function getCustomerSessionTokens()
    {
        $customerId = $this->session->getCustomerId();
        if (!$customerId || $this->session->isLoggedIn() === false) {
            return [];
        }

        return $this->tokenManagement->getVisibleAvailableTokens($customerId);
    }
}
