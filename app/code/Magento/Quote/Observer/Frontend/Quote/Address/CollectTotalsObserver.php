<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Frontend\Quote\Address;

use Magento\Framework\Event\ObserverInterface;

class CollectTotalsObserver implements ObserverInterface
{
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
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Customer\Model\Vat $customerVat,
        VatValidator $vatValidator,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement
    ) {
        $this->customerVat = $customerVat;
        $this->customerAddressHelper = $customerAddressHelper;
        $this->vatValidator = $vatValidator;
        $this->customerDataFactory = $customerDataFactory;
        $this->groupManagement = $groupManagement;
    }

    /**
     * Handle customer VAT number if needed on collect_totals_before event of quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
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
            $customer->setGroupId($groupId);
            $quote->setCustomer($customer);
        }
    }
}
