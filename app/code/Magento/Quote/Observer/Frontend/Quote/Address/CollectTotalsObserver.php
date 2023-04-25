<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Frontend\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;

/**
 * Handle customer VAT number on collect_totals_before event of quote address.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CollectTotalsObserver implements ObserverInterface
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Address
     */
    protected $customerAddressHelper;

    /**
     * @var Vat
     */
    protected $customerVat;

    /**
     * @var VatValidator
     */
    protected $vatValidator;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * Group Management
     *
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * Initialize dependencies.
     *
     * @param Address $customerAddressHelper
     * @param Vat $customerVat
     * @param VatValidator $vatValidator
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param GroupManagementInterface $groupManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Session $customerSession
     */
    public function __construct(
        Address $customerAddressHelper,
        Vat $customerVat,
        VatValidator $vatValidator,
        CustomerInterfaceFactory $customerDataFactory,
        GroupManagementInterface $groupManagement,
        AddressRepositoryInterface $addressRepository,
        Session $customerSession
    ) {
        $this->customerVat = $customerVat;
        $this->customerAddressHelper = $customerAddressHelper;
        $this->vatValidator = $vatValidator;
        $this->customerDataFactory = $customerDataFactory;
        $this->groupManagement = $groupManagement;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Handle customer VAT number if needed on collect_totals_before event of quote address
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $observer->getShippingAssignment();
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        /** @var Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();

        $customer = $quote->getCustomer();
        $storeId = $customer->getStoreId();

        if ($customer->getDisableAutoGroupChange() || !$this->vatValidator->isEnabled($address, $storeId)) {
            return;
        }
        $customerCountryCode = $address->getCountryId();
        $customerVatNumber = $address->getVatId();

        /** try to get data from customer if quote address needed data is empty */
        if (empty($customerCountryCode) && empty($customerVatNumber) && $customer->getDefaultShipping()) {
            $customerAddress = $this->addressRepository->getById($customer->getDefaultShipping());

            $customerCountryCode = $customerAddress->getCountryId();
            $customerVatNumber = $customerAddress->getVatId();
            $address->setCountryId($customerCountryCode);
            $address->setVatId($customerVatNumber);
        }

        $groupId = null;
        if (empty($customerVatNumber) || false == $this->customerVat->isCountryInEU($customerCountryCode)) {
            $groupId = $customer->getId() ? $quote->getCustomerGroupId() :
                $this->groupManagement->getNotLoggedInGroup()->getId();
        } else {
            // Magento always has to emulate group even if customer uses default billing/shipping address
            $groupId = $this->customerVat->getCustomerGroupIdBasedOnVatNumber(
                $customerCountryCode,
                $this->vatValidator->validate($address, $storeId),
                $storeId
            );
        }

        if ($groupId !== null) {
            $address->setPrevQuoteCustomerGroupId($quote->getCustomerGroupId());
            $quote->setCustomerGroupId($groupId);
            $this->customerSession->setCustomerGroupId($groupId);
            $customer->setGroupId($groupId);
            $customer->setEmail($customer->getEmail() ?: $quote->getCustomerEmail());
            $quote->setCustomer($customer);
        }
    }
}
