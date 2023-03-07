<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Weee\Helper\Data;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Customer logged in.
 */
class CustomerLoggedIn implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

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
            /** @var Customer $customer */
            $customer = $observer->getData('customer');
            $addresses = $customer->getAddresses();
            if (isset($addresses)) {
                $this->addressManager->setDefaultAddressAfterLogIn($addresses);
            }
        }
    }
}
