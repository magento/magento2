<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\ModuleManagerInterface;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Tax\Helper\Data;

/**
 * After address save observer.
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $taxHelper;

    /**
     * Module manager
     *
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * Cache config
     *
     * @var Config
     */
    private $cacheConfig;

    /**
     * Manager to save data in customer session.
     *
     * @var TaxAddressManagerInterface
     */
    private $addressManager;

    /**
     * @param Data $taxHelper
     * @param ModuleManagerInterface $moduleManager
     * @param Config $cacheConfig
     * @param TaxAddressManagerInterface $addressManager
     */
    public function __construct(
        Data $taxHelper,
        ModuleManagerInterface $moduleManager,
        Config $cacheConfig,
        TaxAddressManagerInterface $addressManager
    ) {
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
            /** @var $customerAddress Address */
            $address = $observer->getCustomerAddress();
            $this->addressManager->setDefaultAddressAfterSave($address);
        }
    }
}
