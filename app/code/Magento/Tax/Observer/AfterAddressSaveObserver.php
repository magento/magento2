<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Helper\Data;

class AfterAddressSaveObserver implements ObserverInterface
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
     * @param Session $customerSession
     * @param Data $taxHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     */
    public function __construct(
        Session $customerSession,
        Data $taxHelper,
        Manager $moduleManager,
        Config $cacheConfig
    ) {
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
            /** @var $customerAddress Address */
            $address = $observer->getCustomerAddress();

            // Check if the address is either the default billing, shipping, or both
            if ($this->isDefaultBilling($address)) {
                $this->customerSession->setDefaultTaxBillingAddress(
                    [
                        'country_id' => $address->getCountryId(),
                        'region_id'  => $address->getRegion() ? $address->getRegionId() : null,
                        'postcode'   => $address->getPostcode(),
                    ]
                );
            }
            
            if ($this->isDefaultShipping($address)) {
                $this->customerSession->setDefaultTaxShippingAddress(
                    [
                        'country_id' => $address->getCountryId(),
                        'region_id'  => $address->getRegion() ? $address->getRegionId() : null,
                        'postcode'   => $address->getPostcode(),
                    ]
                );
            }
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
