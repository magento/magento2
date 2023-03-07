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
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Weee\Helper\Data;

/**
 * @inheritDoc
 */
class AfterAddressSave implements ObserverInterface
{
    /**
     * @param Data $weeeHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     * @param TaxAddressManagerInterface $addressManager
     */
    public function __construct(
        protected Data $weeeHelper,
        private Manager $moduleManager,
        private Config $cacheConfig,
        private TaxAddressManagerInterface $addressManager
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
            && $this->weeeHelper->isEnabled()
        ) {
            /** @var $customerAddress Address */
            $address = $observer->getCustomerAddress();
            $this->addressManager->setDefaultAddressAfterSave($address);
        }
    }
}
