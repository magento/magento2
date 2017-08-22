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
use Magento\Tax\Helper\Data;

class AfterAddressSaveObserver implements ObserverInterface
{
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
     * @param Data $taxHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     */
    public function __construct(
        Data $taxHelper,
        Manager $moduleManager,
        Config $cacheConfig
    ) {
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
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && $this->taxHelper->isCatalogPriceDisplayAffectedByTax()
        ) {
            /** @var $customerAddress Address */
            $address = $observer->getCustomerAddress();
            $this->taxHelper->setAddressCustomerSessionAddressSave($address);
        }
    }

    /**
     * Check whether specified billing address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultBilling()
        || $address->getIsPrimaryBilling()
        || $address->getIsDefaultBilling();
    }

    /**
     * Check whether specified shipping address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultShipping()
        || $address->getIsPrimaryShipping()
        || $address->getIsDefaultShipping();
    }
}
