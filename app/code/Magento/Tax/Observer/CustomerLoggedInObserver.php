<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Helper\Data;

class CustomerLoggedInObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $taxHelper;

    /**
     * Module manager
     *
     * @var Manager
     */
    private $moduleManager;

    /**
     * Cache config
     *
     * @var Config
     */
    private $cacheConfig;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param Session $customerSession
     * @param Data $taxHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        Session $customerSession,
        Data $taxHelper,
        Manager $moduleManager,
        Config $cacheConfig
    ) {
        $this->groupRepository = $groupRepository;
        $this->customerSession = $customerSession;
        $this->taxHelper = $taxHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache') && $this->cacheConfig->isEnabled() &&
            $this->taxHelper->isCatalogPriceDisplayAffectedByTax()) {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getData('customer');
            $customerGroupId = $customer->getGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerTaxClassId = $customerGroup->getTaxClassId();
            $this->customerSession->setCustomerTaxClassId($customerTaxClassId);

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
