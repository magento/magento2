<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Gateway\Config\Config as BrainTreeConfig;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;

class OneTouchOrdering
{
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var Config
     */
    private $oneTouchHelper;
    /**
     * @var BrainTreeConfig
     */
    private $brainTreeConfig;
    /**
     * @var RateCheck
     */
    private $rateCheck;
    /**
     * @var CustomerBrainTreeManager
     */
    private $customerBrainTreeManager;

    /**
     * OneTouchOrdering constructor.
     * @param Session $customerSession
     * @param CustomerBrainTreeManager $customerBrainTreeManager
     * @param Config $oneTouchConfig
     * @param BrainTreeConfig $brainTreeConfig
     * @param RateCheck $rateCheck
     */
    public function __construct(
        Session $customerSession,
        CustomerBrainTreeManager $customerBrainTreeManager,
        Config $oneTouchConfig,
        BrainTreeConfig $brainTreeConfig,
        RateCheck $rateCheck
    ) {

        $this->customerSession = $customerSession;
        $this->oneTouchHelper = $oneTouchConfig;
        $this->brainTreeConfig = $brainTreeConfig;
        $this->rateCheck = $rateCheck;
        $this->customerBrainTreeManager = $customerBrainTreeManager;
    }

    /**
     * @return bool
     */
    public function isOneTouchOrderingAvailable(): bool
    {
        return $this->isCustomerLoggedIn()
            && $this->isOneTouchButtonEnabled()
            && $this->isBrainTreeAvailable()
            && $this->customerHasDefaultAddresses()
            && $this->isAnyShippingMethodAvailable()
            && $this->customerHasBrainTreeCreditCard();
    }

    /**
     * @return bool
     */
    private function isAnyShippingMethodAvailable(): bool
    {
        $address = $this->getCustomer()->getDefaultShippingAddress();
        return count($this->rateCheck->getRatesForCustomerAddress($address)) > 0;
    }

    /**
     * @return bool
     */
    private function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    private function isOneTouchButtonEnabled(): bool
    {
        return $this->oneTouchHelper->isModuleEnabled();
    }

    /**
     * @return bool
     */
    private function customerHasBrainTreeCreditCard(): bool
    {
        $customerId = $this->customerSession->getCustomerId();
        $ccTokens = $this->customerBrainTreeManager->getVisibleAvailableTokens($customerId);

        return !empty($ccTokens);
    }

    /**
     * @return bool
     */
    private function customerHasDefaultAddresses(): bool
    {
        $customer = $this->getCustomer();
        return $customer->getDefaultBillingAddress() && $customer->getDefaultShippingAddress();
    }

    /**
     * @return bool
     */
    private function isBrainTreeAvailable(): bool
    {
        return $this->brainTreeConfig->isActive();
    }

    /**
     * @return Customer
     */
    private function getCustomer(): Customer
    {
        return $this->customerSession->getCustomer();
    }
}
