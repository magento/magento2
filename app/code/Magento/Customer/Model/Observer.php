<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Model;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var HelperAddress
     */
    protected $_customerAddress;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Vat
     */
    protected $_customerVat;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @param Vat $customerVat
     * @param HelperAddress $customerAddress
     * @param Registry $coreRegistry
     * @param GroupManagementInterface $groupManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param AppState $appState
     */
    public function __construct(
        Vat $customerVat,
        HelperAddress $customerAddress,
        Registry $coreRegistry,
        GroupManagementInterface $groupManagement,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Escaper $escaper,
        AppState $appState
    ) {
        $this->_customerVat = $customerVat;
        $this->_customerAddress = $customerAddress;
        $this->_coreRegistry = $coreRegistry;
        $this->_groupManagement = $groupManagement;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->appState = $appState;
    }

    /**
     * Check whether specified billing address is default for its customer
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultBilling($address)
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
    protected function _isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultShipping()
            || $address->getIsPrimaryShipping()
            || $address->getIsDefaultShipping();
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
        if ($configAddressType == AbstractAddress::TYPE_SHIPPING) {
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

    /**
     * Address after save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterAddressSave($observer)
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();

        if (!$this->_customerAddress->isVatValidationEnabled($customer->getStore())
            || $this->_coreRegistry->registry(self::VIV_PROCESSED_FLAG)
            || !$this->_canProcessAddress($customerAddress)
        ) {
            return;
        }

        try {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, true);

            if ($customerAddress->getVatId() == ''
                || !$this->_customerVat->isCountryInEU($customerAddress->getCountry())
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

                if ($this->appState->getAreaCode() == Area::AREA_FRONTEND) {
                    if ($result->getIsValid()) {
                        $this->addValidMessage($customerAddress, $result);
                    } elseif ($result->getRequestSuccess()) {
                        $this->addInvalidMessage($customerAddress);
                    } else {
                        $this->addErrorMessage($customerAddress);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, false, true);
        }
    }

    /**
     * Add success message for valid VAT ID
     *
     * @param Address $customerAddress
     * @param $validationResult
     * @return $this
     */
    protected function addValidMessage($customerAddress, $validationResult)
    {
        $message = [
            (string)__('Your VAT ID was successfully validated.'),
        ];

        $customer = $customerAddress->getCustomer();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$customer->getDisableAutoGroupChange()
        ) {
            $customerVatClass = $this->_customerVat->getCustomerVatClass(
                $customerAddress->getCountryId(),
                $validationResult
            );
            $message[] = $customerVatClass == Vat::VAT_CLASS_DOMESTIC
                    ? (string)__('You will be charged tax.')
                    : (string)__('You will not be charged tax.');
        }

        $this->messageManager->addSuccess(implode(' ', $message));
        return $this;
    }

    /**
     * Add error message for invalid VAT ID
     *
     * @param Address $customerAddress
     * @return $this
     */
    protected function addInvalidMessage($customerAddress)
    {
        $vatId = $this->escaper->escapeHtml($customerAddress->getVatId());
        $message = [
            (string)__('The VAT ID entered (%1) is not a valid VAT ID.', $vatId),
        ];

        $customer = $customerAddress->getCustomer();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$customer->getDisableAutoGroupChange()
        ) {
            $message[] = (string)__('You will be charged tax.');
        }

        $this->messageManager->addError(implode(' ', $message));
        return $this;
    }

    /**
     * Add error message
     *
     * @param Address $customerAddress
     * @return $this
     */
    protected function addErrorMessage($customerAddress)
    {
        $message = [
            (string)__('Your Tax ID cannot be validated.'),
        ];

        $customer = $customerAddress->getCustomer();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$customer->getDisableAutoGroupChange()
        ) {
            $message[] = (string)__('You will be charged tax.');
        }

        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $message[] = (string)__('If you believe this is an error, please contact us at %1', $email);

        $this->messageManager->addError(implode(' ', $message));
        return $this;
    }
}
