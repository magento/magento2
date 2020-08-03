<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Customer\Api\GroupRepositoryInterface;
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
     * Manager to save data in customer session.
     *
     * @var TaxAddressManagerInterface
     */
    private $addressManager;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param Session $customerSession
     * @param Data $taxHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     * @param TaxAddressManagerInterface $addressManager
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        Session $customerSession,
        Data $taxHelper,
        Manager $moduleManager,
        Config $cacheConfig,
        TaxAddressManagerInterface $addressManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->customerSession = $customerSession;
        $this->taxHelper = $taxHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
        $this->addressManager = $addressManager;
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
            /** @var \Magento\Customer\Model\Data\Customer $customer */
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
