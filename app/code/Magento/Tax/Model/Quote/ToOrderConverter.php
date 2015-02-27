<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Quote;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Model\Quote\Address\ToOrder as QuoteAddressToOrder;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

class ToOrderConverter
{
    /**
     * @var QuoteAddress
     */
    protected $quoteAddress;

    /**
     * @param QuoteAddressToOrder $subject
     * @param QuoteAddress $address
     * @param array $additional
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeConvert(QuoteAddressToOrder $subject, QuoteAddress $address, $additional = [])
    {
        $this->quoteAddress = $address;
        return [$address, $additional];
    }

    /**
     * @param QuoteAddressToOrder $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(QuoteAddressToOrder $subject, OrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $taxes = $this->quoteAddress->getAppliedTaxes();
        if (is_array($taxes)) {
            if (is_array($order->getAppliedTaxes())) {
                $taxes = array_merge($order->getAppliedTaxes(), $taxes);
            }
            $order->setCustomAttribute('applied_taxes', $taxes);
            $order->setCustomAttribute('converting_from_quote', true);
        }

        $itemAppliedTaxes = $this->quoteAddress->getItemsAppliedTaxes();
        if (is_array($itemAppliedTaxes)) {
            if (is_array($order->getItemAppliedTaxes())) {
                $itemAppliedTaxes = array_merge($order->getItemAppliedTaxes(), $itemAppliedTaxes);
            }
            $order->setCustomAttribute('item_applied_taxes', $itemAppliedTaxes);
        }
        return $order;
    }
}
