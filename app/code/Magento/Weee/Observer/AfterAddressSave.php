<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\PageCache\Model\Config;
use Magento\Weee\Helper\Data;
use Magento\Tax\Helper\Data as TaxHelper;

class AfterAddressSave implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $weeeHelper;

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
     * Tax helper
     *
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param Data $weeeHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        Data $weeeHelper,
        Manager $moduleManager,
        Config $cacheConfig,
        TaxHelper $taxHelper
    ) {
        $this->weeeHelper = $weeeHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
        $this->taxHelper = $taxHelper;
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
            && $this->weeeHelper->isEnabled()
        ) {
            /** @var $customerAddress Address */
            $address = $observer->getCustomerAddress();
            $this->taxHelper->setAddressCustomerSessionAddressSave($address);
        }
    }
}
