<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Set persistent data to customer session
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer) || !$this->_persistentData->isShoppingCartPersist()) {
            return $this;
        }

        if ($this->_persistentSession->isPersistent() && !$this->_customerSession->isLoggedIn()) {
            /** @var  \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->customerRepository->getById($this->_persistentSession->getSession()->getCustomerId());
            if ($defaultShipping = $customer->getDefaultShipping()) {
                /** @var  \Magento\Customer\Model\Data\Address $address */
                $address = $this->addressRepository->getById($defaultShipping);
                if ($address) {
                    $this->_customerSession->setDefaultTaxShippingAddress(
                        [
                            'country_id' => $address->getCountryId(),
                            'region_id' => $address->getRegion()
                                ? $address->getRegionId()
                                : null,
                            'postcode' => $address->getPostcode(),
                        ]
                    );
                }
            }

            if ($defaultBilling = $customer->getDefaultBilling()) {
                $address = $this->addressRepository->getById($defaultBilling);
                if ($address) {
                    $this->_customerSession->setDefaultTaxBillingAddress([
                        'country_id' => $address->getCountryId(),
                        'region_id' => $address->getRegion() ? $address->getRegionId() : null,
                        'postcode' => $address->getPostcode(),
                    ]);
                }
            }
            $this->_customerSession
                ->setCustomerId($customer->getId())
                ->setCustomerGroupId($customer->getGroupId())
                ->setIsCustomerEmulated(true);
        }
        return $this;
    }
}
