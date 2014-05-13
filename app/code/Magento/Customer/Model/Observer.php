<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model;

/**
 * Customer module observer
 */
class Observer
{
    /**
     * VAT ID validation processed flag code
     */
    const VIV_PROCESSED_FLAG = 'viv_after_address_save_processed';

    /**
     * VAT ID validation currently saved address flag
     */
    const VIV_CURRENTLY_SAVED_ADDRESS = 'currently_saved_address';

    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_customerAddress = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerData;

    /**
     * @param \Magento\Customer\Helper\Data $customerData
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Customer\Helper\Data $customerData,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_customerData = $customerData;
        $this->_customerAddress = $customerAddress;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Check whether specified billing address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultBilling() ||
            $address->getIsPrimaryBilling() ||
            $address->getIsDefaultBilling();
    }

    /**
     * Check whether specified shipping address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultShipping() ||
            $address->getIsPrimaryShipping() ||
            $address->getIsDefaultShipping();
    }

    /**
     * Check whether specified address should be processed in after_save event handler
     *
     * @param Address $address
     * @return bool
     */
    protected function _canProcessAddress($address)
    {
        if ($address->getForceProcess()) {
            return true;
        }

        if ($this->_coreRegistry->registry(self::VIV_CURRENTLY_SAVED_ADDRESS) != $address->getId()) {
            return false;
        }

        $configAddressType = $this->_customerAddress->getTaxCalculationAddressType();
        if ($configAddressType == \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING) {
            return $this->_isDefaultShipping($address);
        }
        return $this->_isDefaultBilling($address);
    }

    /**
     * Address before save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function beforeAddressSave($observer)
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

            $forceProcess = $configAddressType ==
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING ? $customerAddress->getIsDefaultShipping() : $customerAddress
                    ->getIsDefaultBilling();

            if ($forceProcess) {
                $customerAddress->setForceProcess(true);
            } else {
                $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address');
            }
        }
    }

    /**
     * Address after save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function afterAddressSave($observer)
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();

        if (!$this->_customerAddress->isVatValidationEnabled(
            $customer->getStore()
        ) || $this->_coreRegistry->registry(
            self::VIV_PROCESSED_FLAG
        ) || !$this->_canProcessAddress(
            $customerAddress
        )
        ) {
            return;
        }

        try {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, true);

            /** @var $customerHelper \Magento\Customer\Helper\Data */
            $customerHelper = $this->_customerData;

            if ($customerAddress->getVatId() == '' || !$this->_customerData->isCountryInEU(
                $customerAddress->getCountry()
            )
            ) {
                $defaultGroupId = $customerHelper->getDefaultCustomerGroupId($customer->getStore());

                if (!$customer->getDisableAutoGroupChange() && $customer->getGroupId() != $defaultGroupId) {
                    $customer->setGroupId($defaultGroupId);
                    $customer->save();
                }
            } else {
                $result = $customerHelper->checkVatNumber(
                    $customerAddress->getCountryId(),
                    $customerAddress->getVatId()
                );

                $newGroupId = $customerHelper->getCustomerGroupIdBasedOnVatNumber(
                    $customerAddress->getCountryId(),
                    $result,
                    $customer->getStore()
                );

                if (!$customer->getDisableAutoGroupChange() && $customer->getGroupId() != $newGroupId) {
                    $customer->setGroupId($newGroupId);
                    $customer->save();
                }

                $customerAddress->setVatValidationResult($result);
            }
        } catch (\Exception $e) {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, false, true);
        }
    }
}
