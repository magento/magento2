<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Tax\Helper\Data;

/**
 * After address save observer.
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * @param Data $taxHelper
     * @param Manager $moduleManager Module manager
     * @param Config $cacheConfig Cache config
     * @param TaxAddressManagerInterface $addressManager Manager to save data in customer session.
     */
    public function __construct(
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
            /** @var Address $customerAddress */
            $address = $observer->getCustomerAddress();
            $this->addressManager->setDefaultAddressAfterSave($address);
        }
    }
}
