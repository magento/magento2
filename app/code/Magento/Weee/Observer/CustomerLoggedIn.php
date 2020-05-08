<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

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
     * @var Data
     */
    protected $weeeHelper;

    /**
     * Manager to save data in customer session.
     *
     * @var TaxAddressManagerInterface
     */
    private $addressManager;

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
     * @param Data $weeeHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     * @param TaxAddressManagerInterface $addressManager
     */
    public function __construct(
        Data $weeeHelper,
        Manager $moduleManager,
        Config $cacheConfig,
        TaxAddressManagerInterface $addressManager
    ) {
        $this->weeeHelper = $weeeHelper;
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
            && $this->weeeHelper->isEnabled()
        ) {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getData('customer');
            $addresses = $customer->getAddresses();
            if (isset($addresses)) {
                $this->addressManager->setDefaultAddressAfterLogIn($addresses);
            }
        }
    }
}
