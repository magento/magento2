<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address as HelperAddress;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer Observer Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * VAT ID validation processed flag code
     */
    public const VIV_PROCESSED_FLAG = 'viv_after_address_save_processed';

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
     * @var AppState
     */
    protected $appState;

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
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Vat $customerVat
     * @param HelperAddress $customerAddress
     * @param Registry $coreRegistry
     * @param GroupManagementInterface $groupManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param AppState $appState
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Vat $customerVat,
        HelperAddress $customerAddress,
        Registry $coreRegistry,
        GroupManagementInterface $groupManagement,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Escaper $escaper,
        AppState $appState,
        CustomerSession $customerSession
    ) {
        $this->_customerVat = $customerVat;
        $this->_customerAddress = $customerAddress;
        $this->_coreRegistry = $coreRegistry;
        $this->_groupManagement = $groupManagement;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->appState = $appState;
        $this->customerSession = $customerSession;
    }

    /**
     * Address after save event handler
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var $customerAddress Address */
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();

        if (!$this->_customerAddress->isVatValidationEnabled($customer->getStore())
            || $this->_coreRegistry->registry(self::VIV_PROCESSED_FLAG)
            || !$this->_canProcessAddress($customerAddress)
            || $customerAddress->getShouldIgnoreValidation()
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
                    $this->customerSession->setCustomerGroupId($defaultGroupId);
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
                    $this->customerSession->setCustomerGroupId($newGroupId);
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

        if ($this->_coreRegistry->registry(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS) != $address->getId()
        ) {
            return false;
        }

        $configAddressType = $this->_customerAddress->getTaxCalculationAddressType();
        if ($configAddressType == AbstractAddress::TYPE_SHIPPING) {
            return $this->_isDefaultShipping($address);
        }

        return $this->_isDefaultBilling($address);
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
     * Add success message for valid VAT ID
     *
     * @param Address $customerAddress
     * @param DataObject $validationResult
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

        $this->messageManager->addErrorMessage(implode(' ', $message));

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

        $this->messageManager->addErrorMessage(implode(' ', $message));

        return $this;
    }
}
