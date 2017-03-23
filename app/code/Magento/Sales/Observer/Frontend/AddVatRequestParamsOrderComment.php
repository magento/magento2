<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer\Frontend;

use Magento\Customer\Helper\Address as CustomerAddress;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;

/**
 * Class AddVatRequestParamsOrderComment
 */
class AddVatRequestParamsOrderComment implements ObserverInterface
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
     * Add VAT validation request date and identifier to order comments
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var $orderInstance Order */
        $orderInstance = $observer->getOrder();
        /** @var $orderAddress Address */
        $orderAddress = $this->_getVatRequiredSalesAddress($orderInstance);
        if (!$orderAddress instanceof Address) {
            return;
        }

        $vatRequestId = $orderAddress->getVatRequestId();
        $vatRequestDate = $orderAddress->getVatRequestDate();
        if (is_string($vatRequestId)
            && !empty($vatRequestId)
            && is_string($vatRequestDate)
            && !empty($vatRequestDate)
        ) {
            $orderHistoryComment = __('VAT Request Identifier')
                . ': ' . $vatRequestId . '<br />'
                . __('VAT Request Date') . ': ' . $vatRequestDate;
            $orderInstance->addStatusHistoryComment($orderHistoryComment, false);
        }
    }

    /**
     * Retrieve sales address (order or quote) on which tax calculation must be based
     *
     * @param Order $order
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return Address|null
     */
    protected function _getVatRequiredSalesAddress($order, $store = null)
    {
        $configAddressType = $this->customerAddressHelper->getTaxCalculationAddressType($store);
        $requiredAddress = $configAddressType === AbstractAddress::TYPE_SHIPPING
            ? $order->getShippingAddress()
            : $order->getBillingAddress();

        return $requiredAddress;
    }
}
