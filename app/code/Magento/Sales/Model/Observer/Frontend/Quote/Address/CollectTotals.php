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
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerAddress;

    /**
     * Customer data
     *
     * @var \Magento\Customer\Helper\Data
     */
    protected $customerData;

    /**
     * @var VatValidator
     */
    protected $vatValidator;

    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Customer\Helper\Data $customerData
     * @param VatValidator $vatValidator
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Customer\Helper\Data $customerData,
        VatValidator $vatValidator
    ) {
        $this->customerData = $customerData;
        $this->customerAddress = $customerAddress;
        $this->vatValidator = $vatValidator;
    }

    /**
     * Handle customer VAT number if needed on collect_totals_before event of quote address
     *
     * @param \Magento\Event\Observer $observer
     */
    public function dispatch(\Magento\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Quote\Address $quoteAddress */
        $quoteAddress = $observer->getQuoteAddress();

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $quoteAddress->getQuote();

        /** TODO: References to Magento\Customer\Model\Customer will be eliminated in scope of MAGETWO-19763 */
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $quote->getCustomer();

        /** @var \Magento\Core\Model\Store $store */
        $store = $customer->getStore();

        if ($customer->getDisableAutoGroupChange() || false == $this->vatValidator->isEnabled($quoteAddress, $store)) {
            return;
        }

        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber   = $quoteAddress->getVatId();
        $groupId = null;

        if (empty($customerVatNumber) || false == $this->customerData->isCountryInEU($customerCountryCode)) {
            $groupId = $customer->getId()
                ? $this->customerData->getDefaultCustomerGroupId($store)
                /** TODO: References to Magento\Customer\Model\Group will be eliminated in scope of MAGETWO-19763 */
                : \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        } else {
            // Magento always has to emulate group even if customer uses default billing/shipping address
            $groupId = $this->customerData->getCustomerGroupIdBasedOnVatNumber(
                $customerCountryCode, $this->vatValidator->validate($quoteAddress, $store), $store
            );
        }

        if ($groupId) {
            $quoteAddress->setPrevQuoteCustomerGroupId($quote->getCustomerGroupId());
            $customer->setGroupId($groupId);
            $quote->setCustomerGroupId($groupId);
        }
    }
}
