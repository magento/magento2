<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Vault\Model;

use Magento\Customer\Model\Session;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class CustomerTokenManagement
{
    /**
     * @var PaymentTokenManagement
     */
    private $tokenManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * CustomerTokenManagement constructor.
     * @param PaymentTokenManagement $tokenManagement
     * @param Session $session
     */
    public function __construct(
        PaymentTokenManagement $tokenManagement,
        Session $session
    ) {
        $this->tokenManagement = $tokenManagement;
        $this->session = $session;
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
