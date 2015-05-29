<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */


/**
 * Customer Session Event Observer
 */
namespace Magento\Tax\Model\Observer;

class Session
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->groupRepository = $groupRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function customerLoggedIn(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $observer->getData('customer');
        $customerGroupId = $customer->getGroupId();
        $customerGroup = $this->groupRepository->getById($customerGroupId);
        $customerTaxClassId = $customerGroup->getTaxClassId();
        $this->customerSession->setCustomerTaxClassId($customerTaxClassId);

        /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
        $addresses = $customer->getAddresses();
        if (!isset($addresses)) {
            return;
        }
        $defaultShippingFound = false;
        $defaultBillingFound = false;
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $defaultBillingFound = true;
                $this->customerSession->setDefaultTaxBillingAddress(
                    [
                        'country_id' => $address->getCountryId(),
                        'region_id' => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                        'postcode' => $address->getPostcode(),
                    ]
                );
            }
            if ($address->isDefaultShipping()) {
                $defaultShippingFound = true;
                $this->customerSession->setDefaultTaxShippingAddress(
                    [
                        'country_id' => $address->getCountryId(),
                        'region_id' => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                        'postcode' => $address->getPostcode(),
                    ]
                );
            }
            if ($defaultShippingFound && $defaultBillingFound) {
                break;
            }
        }
    }
}
