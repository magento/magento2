<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Frontend;

use Magento\Customer\Helper\Address as CustomerAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;

/**
 * Class RestoreCustomerGroupId
 * @since 2.0.0
 */
class RestoreCustomerGroupId implements ObserverInterface
{
    /**
     * Customer address
     *
     * @var CustomerAddress
     * @since 2.0.0
     */
    protected $customerAddressHelper;

    /**
     * @param CustomerAddress $customerAddressHelper
     * @since 2.0.0
     */
    public function __construct(CustomerAddress $customerAddressHelper)
    {
        $this->customerAddressHelper = $customerAddressHelper;
    }

    /**
     * Restore initial customer group ID in quote if needed on collect_totals_after event of quote address
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(Observer $observer)
    {
        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $observer->getEvent()->getShippingAssignment();
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $address = $shippingAssignment->getShipping()->getAddress();
        $configAddressType = $this->customerAddressHelper->getTaxCalculationAddressType();
        // Restore initial customer group ID in quote only if VAT is calculated based on shipping address
        if ($address->hasPrevQuoteCustomerGroupId() &&
            $configAddressType == AbstractAddress::TYPE_SHIPPING
        ) {
            $quote->setCustomerGroupId($address->getPrevQuoteCustomerGroupId());
            $address->unsPrevQuoteCustomerGroupId();
        }
    }
}
