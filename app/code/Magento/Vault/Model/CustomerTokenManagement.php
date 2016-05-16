<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class CustomerTokenManagement
{
    /**
     * @var VaultPaymentInterface
     */
    private $vaultPayment;

    /**
     * @var PaymentTokenManagement
     */
    private $tokenManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CustomerTokenManagement constructor.
     * @param VaultPaymentInterface $vaultPayment
     * @param PaymentTokenManagement $tokenManagement
     * @param Session $session
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        VaultPaymentInterface $vaultPayment,
        PaymentTokenManagement $tokenManagement,
        Session $session,
        StoreManagerInterface $storeManager
    ) {
        $this->vaultPayment = $vaultPayment;
        $this->tokenManagement = $tokenManagement;
        $this->session = $session;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns list of payment tokens for current customer session
     *
     * @return PaymentTokenInterface[]
     */
    public function getCustomerSessionTokens()
    {
        $vaultPayments = [];

        $customerId = $this->session->getCustomerId();
        if (!$customerId) {
            return $vaultPayments;
        }

        $storeId = $this->storeManager->getStore()->getId();
        if (!$this->vaultPayment->isActive($storeId)) {
            return $vaultPayments;
        }

        $providerCode = $this->vaultPayment->getProviderCode($storeId);

        return $this->tokenManagement->getVisibleAvailableTokens($customerId, $providerCode);
    }
}
