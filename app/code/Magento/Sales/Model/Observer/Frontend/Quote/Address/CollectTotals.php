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
        $customerData = $quote->getCustomerData();
        $storeId = $customerData->getStoreId();

        if (($customerData->getCustomAttribute('disable_auto_group_change')
                && $customerData->getCustomAttribute('disable_auto_group_change')->getValue())
            || false == $this->vatValidator->isEnabled($quoteAddress, $storeId)
        ) {
            return;
        }

        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber = $quoteAddress->getVatId();
        $groupId = null;
        if (empty($customerVatNumber) || false == $this->customerVat->isCountryInEU($customerCountryCode)) {
            $groupId = $customerData->getId()
                ? $this->groupManagement->getDefaultGroup($storeId)->getId()
                : $this->groupManagement->getNotLoggedInGroup()->getId();
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
            $customerData = $this->customerBuilder->mergeDataObjectWithArray(
                $customerData,
                array('group_id' => $groupId)
            )->create();
            $quote->setCustomerData($customerData);
        }
    }
}
