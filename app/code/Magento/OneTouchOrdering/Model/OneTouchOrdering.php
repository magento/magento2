<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

class OneTouchOrdering
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\OneTouchOrdering\Model\Config
     */
    private $oneTouchHelper;
    /**
     * @var \Magento\Braintree\Gateway\Config\Config
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
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerBrainTreeManager $customerBrainTreeManager
     * @param \Magento\OneTouchOrdering\Model\Config $oneTouchConfig
     * @param \Magento\Braintree\Gateway\Config\Config $brainTreeConfig
     * @param RateCheck $rateCheck
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\OneTouchOrdering\Model\CustomerBrainTreeManager $customerBrainTreeManager,
        \Magento\OneTouchOrdering\Model\Config $oneTouchConfig,
        \Magento\Braintree\Gateway\Config\Config $brainTreeConfig,
        \Magento\OneTouchOrdering\Model\RateCheck $rateCheck
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
    public function isOneTouchOrderingAvailable()
    {
        return $this->isCustomerLoggedIn()
            && $this->isOneTouchButtonEnabled()
            && $this->isBrainTreeAvailable()
            && $this->customerHasDefaultAddresses()
            && $this->isAnyShippingMethodAvailable()
            && $this->customerHasBrainTreeCreditCard();
    }

    /**
     * @return int|void
     */
    private function isAnyShippingMethodAvailable()
    {
        $address = $this->getCustomer()->getDefaultShippingAddress();
        return count($this->rateCheck->getRatesForCustomerAddress($address) > 0);
    }

    /**
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    private function isOneTouchButtonEnabled()
    {
        return $this->oneTouchHelper->isModuleEnabled();
    }

    /**
     * @return bool
     */
    private function customerHasBrainTreeCreditCard()
    {
        $customerId = $this->customerSession->getCustomerId();
        $ccTokens = $this->customerBrainTreeManager->getVisibleAvailableTokens($customerId);

        return !empty($ccTokens);
    }

    /**
     * @return bool
     */
    private function customerHasDefaultAddresses()
    {
        $customer = $this->getCustomer();
        return $customer->getDefaultBillingAddress() && $customer->getDefaultShippingAddress();
    }

    /**
     * @return bool
     */
    private function isBrainTreeAvailable()
    {
        return $this->brainTreeConfig->isActive();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
