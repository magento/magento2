<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote;

use Magento\Customer\Helper\Address as CustomerAddress;

/**
 * Class AddVatRequestParamsOrderComment
 */
class AddVatRequestParamsOrderComment
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance \Magento\Sales\Model\Order */
        $orderInstance = $observer->getOrder();
        /** @var $orderAddress \Magento\Sales\Model\Order\Address */
        $orderAddress = $this->_getVatRequiredSalesAddress($orderInstance);
        if (!$orderAddress instanceof \Magento\Sales\Model\Order\Address) {
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
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Store\Model\Store|string|int|null $store
     * @return \Magento\Sales\Model\Order\Address|null
     */
    protected function _getVatRequiredSalesAddress($order, $store = null)
    {
        $configAddressType = $this->customerAddressHelper->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING:
                $requiredAddress = $order->getShippingAddress();
                break;
            default:
                $requiredAddress = $order->getBillingAddress();
                break;
        }
        return $requiredAddress;
    }
}
