<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Frontend\Quote\Address;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver
 *
 */
class CollectTotalsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     * @since 2.2.0
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.2.0
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerAddressHelper;

    /**
     * @var \Magento\Customer\Model\Vat
     */
    protected $customerVat;

    /**
     * @var VatValidator
     */
    protected $vatValidator;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * Group Management
     *
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Customer\Helper\Address $customerAddressHelper
     * @param \Magento\Customer\Model\Vat $customerVat
     * @param VatValidator $vatValidator
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Customer\Model\Vat $customerVat,
        VatValidator $vatValidator,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Session $customerSession
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $observer->getShippingAssignment();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();

        $customer = $quote->getCustomer();
        $storeId = $customer->getStoreId();

        if ($customer->getDisableAutoGroupChange()
            || false == $this->vatValidator->isEnabled($address, $storeId)
        ) {
            return;
        }
        $customerCountryCode = $address->getCountryId();
        $customerVatNumber = $address->getVatId();

        /** try to get data from customer if quote address needed data is empty */
        if (empty($customerCountryCode) && empty($customerVatNumber) && $customer->getDefaultShipping()) {
            $customerAddress = $this->addressRepository->getById($customer->getDefaultShipping());

            $customerCountryCode = $customerAddress->getCountryId();
            $customerVatNumber = $customerAddress->getVatId();
        }

        $groupId = null;
        if (empty($customerVatNumber) || false == $this->customerVat->isCountryInEU($customerCountryCode)) {
            $groupId = $customer->getId() ? $this->groupManagement->getDefaultGroup(
                $storeId
            )->getId() : $this->groupManagement->getNotLoggedInGroup()->getId();
        } else {
            // Magento always has to emulate group even if customer uses default billing/shipping address
            $groupId = $this->customerVat->getCustomerGroupIdBasedOnVatNumber(
                $customerCountryCode,
                $this->vatValidator->validate($address, $storeId),
                $storeId
            );
        }

        if ($groupId) {
            $address->setPrevQuoteCustomerGroupId($quote->getCustomerGroupId());
            $quote->setCustomerGroupId($groupId);
            $this->customerSession->setCustomerGroupId($groupId);
            $customer->setGroupId($groupId);
            $quote->setCustomer($customer);
        }
    }
}
