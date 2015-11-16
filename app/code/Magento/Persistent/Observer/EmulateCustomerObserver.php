<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class EmulateCustomer
 */
class EmulateCustomerObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Set persistent data to customer session
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer) || !$this->_persistentData->isShoppingCartPersist()) {
            return $this;
        }

        if ($this->_persistentSession->isPersistent() && !$this->_customerSession->isLoggedIn()) {
            /** @var  \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerRepository->getById($this->_persistentSession->getSession()->getCustomerId());
            $defaultShippingAddress = $customer->getDefaultShippingAddress();
            if ($defaultShippingAddress) {
                $this->_customerSession->setDefaultTaxShippingAddress(
                    [
                        'country_id' => $defaultShippingAddress->getCountryId(),
                        'region_id'  => $defaultShippingAddress->getRegion() ? $defaultShippingAddress->getRegionId() : null,
                        'postcode'   => $defaultShippingAddress->getPostcode(),
                    ]);
            }

            $defaultBillingAddress = $customer->getDefaultBillingAddress();
            if ($defaultBillingAddress){
                $this->_customerSession->setDefaultTaxBillingAddress([
                    'country_id' => $defaultBillingAddress->getCountryId(),
                    'region_id'  => $defaultBillingAddress->getRegion() ? $defaultBillingAddress->getRegionId() : null,
                    'postcode'   => $defaultBillingAddress->getPostcode(),
                ]);
            }
           $this->_customerSession
                ->setCustomerId($customer->getId())
                ->setCustomerGroupId($customer->getGroupId());
        }
        return $this;
    }
}
