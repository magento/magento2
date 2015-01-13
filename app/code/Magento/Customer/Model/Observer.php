<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var \Magento\Customer\Model\Vat
     */
    protected $_customerVat;

    /**
     * Group Management
     *
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @param \Magento\Customer\Model\Vat $customerVat
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Customer\Model\Vat $customerVat,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement
    ) {
        $this->_customerVat = $customerVat;
        $this->_customerAddress = $customerAddress;
        $this->_coreRegistry = $coreRegistry;
        $this->_groupManagement = $groupManagement;
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

            if ($customerAddress->getVatId() == '' || !$this->_customerVat->isCountryInEU(
                $customerAddress->getCountry()
            )
            ) {
                $defaultGroupId = $this->_groupManagement->getDefaultGroup($customer->getStore())->getId();

                if (!$customer->getDisableAutoGroupChange() && $customer->getGroupId() != $defaultGroupId) {
                    $customer->setGroupId($defaultGroupId);
                    $customer->save();
                }
            } else {
                $result = $this->_customerVat->checkVatNumber(
                    $customerAddress->getCountryId(),
                    $customerAddress->getVatId()
                );

                $newGroupId = $this->_customerVat->getCustomerGroupIdBasedOnVatNumber(
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
