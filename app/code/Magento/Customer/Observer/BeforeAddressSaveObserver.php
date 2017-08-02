<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;

/**
 * Customer Observer Model
 * @since 2.0.0
 */
class BeforeAddressSaveObserver implements ObserverInterface
{
    /**
     * VAT ID validation currently saved address flag
     */
    const VIV_CURRENTLY_SAVED_ADDRESS = 'currently_saved_address';

    /**
     * @var HelperAddress
     * @since 2.0.0
     */
    protected $_customerAddress;

    /**
     * @var Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @param HelperAddress $customerAddress
     * @param Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(
        HelperAddress $customerAddress,
        Registry $coreRegistry
    ) {
        $this->_customerAddress = $customerAddress;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Address before save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry(self::VIV_CURRENTLY_SAVED_ADDRESS)) {
            $this->_coreRegistry->unregister(self::VIV_CURRENTLY_SAVED_ADDRESS);
        }

        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        if ($customerAddress->getId()) {
            $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddress->getId());
        } else {
            $configAddressType = $this->_customerAddress->getTaxCalculationAddressType();
            $forceProcess = $configAddressType == AbstractAddress::TYPE_SHIPPING
                ? $customerAddress->getIsDefaultShipping()
                : $customerAddress->getIsDefaultBilling();
            if ($forceProcess) {
                $customerAddress->setForceProcess(true);
            } else {
                $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address');
            }
        }
    }
}
