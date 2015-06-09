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
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * Cache config
     *
     * @var \Magento\PageCache\Model\Config
     */
    private $cacheConfig;

    /**
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\PageCache\Model\Config $cacheConfig
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\PageCache\Model\Config $cacheConfig
    ) {
        $this->groupRepository = $groupRepository;
        $this->customerSession = $customerSession;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function customerLoggedIn(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache') && $this->cacheConfig->isEnabled()) {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getData('customer');
            $customerGroupId = $customer->getGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerTaxClassId = $customerGroup->getTaxClassId();
            $websiteId = $customer->getWebsiteId();
            $this->customerSession->setCustomerTaxClassId($customerTaxClassId);
            $this->customerSession->setWebsiteId($websiteId);

            /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
            $addresses = $customer->getAddresses();
            if (isset($addresses)) {
                $defaultShippingFound = false;
                $defaultBillingFound = false;
                foreach ($addresses as $address) {
                    if ($address->isDefaultBilling()) {
                        $defaultBillingFound = true;
                        $this->customerSession->setDefaultTaxBillingAddress(
                            [
                                'country_id' => $address->getCountryId(),
                                'region_id'  => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                                'postcode'   => $address->getPostcode(),
                            ]
                        );
                    }
                    if ($address->isDefaultShipping()) {
                        $defaultShippingFound = true;
                        $this->customerSession->setDefaultTaxShippingAddress(
                            [
                                'country_id' => $address->getCountryId(),
                                'region_id'  => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                                'postcode'   => $address->getPostcode(),
                            ]
                        );
                    }
                    if ($defaultShippingFound && $defaultBillingFound) {
                        break;
                    }
                }
            }
        }
    }
}
