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
    protected $customerSession;
    /**
     * @var \Magento\OneTouchOrdering\Model\Config
     */
    protected $oneTouchHelper;
    /**
     * @var \Magento\Braintree\Gateway\Config\Config
     */
    protected $brainTreeConfig;
    /**
     * @var RateCheck
     */
    protected $rateCheck;
    /**
     * @var CustomerBrainTreeManager
     */
    protected $customerBrainTreeManager;

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
    protected function isAnyShippingMethodAvailable()
    {
        $address = $this->getCustomer()->getDefaultShippingAddress();
        return count($this->rateCheck->getRatesForCustomerAddress($address) > 0);
    }

    /**
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    protected function isOneTouchButtonEnabled()
    {
        return $this->oneTouchHelper->isModuleEnabled();
    }

    /**
     * @return bool
     */
    protected function customerHasBrainTreeCreditCard()
    {
        $customerId = $this->customerSession->getCustomerId();
        $ccTokens = $this->customerBrainTreeManager->getVisibleAvailableTokens($customerId);

        return !empty($ccTokens);
    }

    /**
     * @return bool
     */
    protected function customerHasDefaultAddresses()
    {
        $customer = $this->getCustomer();
        return $customer->getDefaultBillingAddress() && $customer->getDefaultShippingAddress();
    }

    /**
     * @return bool
     */
    protected function isBrainTreeAvailable()
    {
        return $this->brainTreeConfig->isActive();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
