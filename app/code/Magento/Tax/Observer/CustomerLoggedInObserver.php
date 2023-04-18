<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Tax\Helper\Data;

/**
 * Customer logged in observer
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CustomerLoggedInObserver implements ObserverInterface
{
    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param Session $customerSession
     * @param Data $taxHelper
     * @param Manager $moduleManager Module manager
     * @param Config $cacheConfig Cache config
     * @param TaxAddressManagerInterface $addressManager Manager to save data in customer session.
     */
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
        protected readonly Session $customerSession,
        protected readonly Data $taxHelper,
        private readonly Manager $moduleManager,
        private readonly Config $cacheConfig,
        private readonly TaxAddressManagerInterface $addressManager
    ) {
    }

    /**
     * Execute.
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && $this->taxHelper->isCatalogPriceDisplayAffectedByTax()
        ) {
            /** @var Customer $customer */
            $customer = $observer->getData('customer');
            $customerGroupId = $customer->getGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerTaxClassId = $customerGroup->getTaxClassId();
            $this->customerSession->setCustomerTaxClassId($customerTaxClassId);

            $addresses = $customer->getAddresses();
            if (isset($addresses)) {
                $this->addressManager->setDefaultAddressAfterLogIn($addresses);
            }
        }
    }
}
