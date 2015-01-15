<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote\Address;

class CollectTotals
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
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $customerBuilder;

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
     * @param \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Customer\Model\Vat $customerVat,
        VatValidator $vatValidator,
        \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement
    ) {
        $this->customerVat = $customerVat;
        $this->customerAddressHelper = $customerAddressHelper;
        $this->vatValidator = $vatValidator;
        $this->customerBuilder = $customerBuilder;
        $this->groupManagement = $groupManagement;
    }

    /**
     * Handle customer VAT number if needed on collect_totals_before event of quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function dispatch(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Quote\Address $quoteAddress */
        $quoteAddress = $observer->getQuoteAddress();

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $quoteAddress->getQuote();
        $customer = $quote->getCustomer();
        $storeId = $customer->getStoreId();

        if (($customer->getCustomAttribute('disable_auto_group_change')
                && $customer->getCustomAttribute('disable_auto_group_change')->getValue())
            || false == $this->vatValidator->isEnabled($quoteAddress, $storeId)
        ) {
            return;
        }

        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber = $quoteAddress->getVatId();
        $groupId = null;
        if (empty($customerVatNumber) || false == $this->customerVat->isCountryInEU($customerCountryCode)) {
            $groupId = $customer->getId() ? $this->groupManagement->getDefaultGroup(
                $storeId
            )->getId() : $this->groupManagement->getNotLoggedInGroup()->getId();
        } else {
            // Magento always has to emulate group even if customer uses default billing/shipping address
            $groupId = $this->customerVat->getCustomerGroupIdBasedOnVatNumber(
                $customerCountryCode,
                $this->vatValidator->validate($quoteAddress, $storeId),
                $storeId
            );
        }

        if ($groupId) {
            $quoteAddress->setPrevQuoteCustomerGroupId($quote->getCustomerGroupId());
            $quote->setCustomerGroupId($groupId);
            $customer = $this->customerBuilder->mergeDataObjectWithArray($customer, ['group_id' => $groupId])->create();
            $quote->setCustomer($customer);
        }
    }
}
