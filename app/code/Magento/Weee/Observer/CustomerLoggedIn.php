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
use Magento\Weee\Helper\Data;

/**
 * Class \Magento\Weee\Observer\CustomerLoggedIn
 *
 * @since 2.0.0
 */
class CustomerLoggedIn implements ObserverInterface
{
    /**
     * @var Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var Data
     * @since 2.0.0
     */
    protected $weeeHelper;

    /**
     * Module manager
     *
     * @var Manager
     * @since 2.0.0
     */
    private $moduleManager;

    /**
     * Cache config
     *
     * @var Config
     * @since 2.0.0
     */
    private $cacheConfig;

    /**
     * @param Session $customerSession
     * @param Data $weeeHelper
     * @param Manager $moduleManager
     * @param Config $cacheConfig
     * @since 2.0.0
     */
    public function __construct(
        Session $customerSession,
        Data $weeeHelper,
        Manager $moduleManager,
        Config $cacheConfig
    ) {
        $this->customerSession = $customerSession;
        $this->weeeHelper = $weeeHelper;
        $this->moduleManager = $moduleManager;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache') && $this->cacheConfig->isEnabled() &&
            $this->weeeHelper->isEnabled()) {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $observer->getData('customer');

            /** @var \Magento\Customer\Api\Data\AddressInterface[] $addresses */
            $addresses = $customer->getAddresses();
            if (isset($addresses)) {
                $defaultShippingFound = false;
                $defaultBillingFound = false;
                foreach ($addresses as $address) {
                    if ($address->isDefaultBilling()) {
                        $defaultBillingFound = true;
                        $this->customerSession->setDefaultTaxBillingAddress(
                            [
                                'country_id' => $address->getCountryId(),
                                'region_id'  => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                                'postcode'   => $address->getPostcode(),
                            ]
                        );
                    }
                    if ($address->isDefaultShipping()) {
                        $defaultShippingFound = true;
                        $this->customerSession->setDefaultTaxShippingAddress(
                            [
                                'country_id' => $address->getCountryId(),
                                'region_id'  => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                                'postcode'   => $address->getPostcode(),
                            ]
                        );
                    }
                    if ($defaultShippingFound && $defaultBillingFound) {
                        break;
                    }
                }
            }
        }
    }
}
