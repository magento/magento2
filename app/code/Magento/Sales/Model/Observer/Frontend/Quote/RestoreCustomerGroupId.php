<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote;

use Magento\Customer\Helper\Address as CustomerAddress;

/**
 * Class RestoreCustomerGroupId
 */
class RestoreCustomerGroupId
{
    /**
     * Customer address
     *
     * @var CustomerAddress
     */
    protected $customerAddressHelper;

    /**
     * @param CustomerAddress $customerAddressHelper
     */
    public function __construct(CustomerAddress $customerAddressHelper)
    {
        $this->customerAddressHelper = $customerAddressHelper;
    }

    /**
     * Restore initial customer group ID in quote if needed on collect_totals_after event of quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute($observer)
    {
        $quoteAddress = $observer->getQuoteAddress();
        $configAddressType = $this->customerAddressHelper->getTaxCalculationAddressType();
        // Restore initial customer group ID in quote only if VAT is calculated based on shipping address
        if ($quoteAddress->hasPrevQuoteCustomerGroupId() &&
            $configAddressType == \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING
        ) {
            $quoteAddress->getQuote()->setCustomerGroupId($quoteAddress->getPrevQuoteCustomerGroupId());
            $quoteAddress->unsPrevQuoteCustomerGroupId();
        }
    }
}
