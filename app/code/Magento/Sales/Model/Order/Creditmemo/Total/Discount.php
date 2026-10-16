<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

use Magento\Tax\Model\Config;

/**
 * Discount total calculator
 */
class Discount extends AbstractTotal
{
    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @param Config $taxConfig
     * @param array $data
     */
    public function __construct(
        Config $taxConfig,
        array $data = []
    ) {
        $this->taxConfig = $taxConfig;

        parent::__construct($data);
    }

    /**
     * Collect discount
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setDiscountAmount(0);
        $creditmemo->setBaseDiscountAmount(0);

        $order = $creditmemo->getOrder();

        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        /**
         * Calculate how much shipping discount should be applied
         * basing on how much shipping should be refunded.
         */
        $baseShippingAmount = $this->getBaseShippingAmount($creditmemo, $order);

        /**
         * If credit memo's shipping amount is set and Order's shipping amount is 0,
         * throw exception with different message
         */
        if ($baseShippingAmount && $order->getBaseShippingAmount() <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("You can not refund shipping if there is no shipping amount.")
            );
        }
        if ($baseShippingAmount) {
            $orderBaseShippingAmount =  $this->isShippingInclTax((int)$order->getStoreId()) ?
                $order->getBaseShippingInclTax() : $order->getBaseShippingAmount();
            $orderShippingAmount =  $this->isShippingInclTax((int)$order->getStoreId()) ?
                $order->getShippingInclTax() : $order->getShippingAmount();
            $baseShippingDiscount = $baseShippingAmount *
                $order->getBaseShippingDiscountAmount() /
                $orderBaseShippingAmount;
            $shippingDiscount = $orderShippingAmount * $baseShippingDiscount / $orderBaseShippingAmount;
            $totalDiscountAmount = $totalDiscountAmount + $shippingDiscount;
            $baseTotalDiscountAmount = $baseTotalDiscountAmount + $baseShippingDiscount;
        }

        /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount = (float)$orderItem->getDiscountInvoiced();
            $baseOrderItemDiscount = (float)$orderItem->getBaseDiscountInvoiced();
            $orderItemQty = $orderItem->getQtyInvoiced();

            if ($orderItemDiscount && $orderItemQty) {
                $discount = $orderItemDiscount - $orderItem->getDiscountRefunded();
                $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $discount = $creditmemo->roundPrice($discount / $availableQty * $item->getQty(), 'regular', true);
                    $baseDiscount = $creditmemo->roundPrice(
                        $baseDiscount / $availableQty * $item->getQty(),
                        'base',
                        true
                    );
                }

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($baseDiscount);

                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }

        $creditmemo->setDiscountAmount(-$totalDiscountAmount);
        $creditmemo->setBaseDiscountAmount(-$baseTotalDiscountAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);
        return $this;
    }

    /**
     * Get the base shipping amount the credit memo refunds
     *
     * Mirrors the shipping total, which runs after this one: a requested amount is refunded as is, even 0, and
     * without one all the shipping not refunded yet is. The credit memo's shipping tax fields can't be used to
     * work it out, because until the shipping and tax totals have run they still hold the order's values.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    private function getBaseShippingAmount(
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Sales\Model\Order $order
    ): float {
        if ($creditmemo->hasBaseShippingAmount()) {
            return (float)$creditmemo->getBaseShippingAmount();
        }

        $orderBaseShippingAmount = (float)$order->getBaseShippingAmount();
        $baseShippingAmount = max($orderBaseShippingAmount - (float)$order->getBaseShippingRefunded(), 0.0);
        if ($this->isShippingInclTax((int)$order->getStoreId()) && $orderBaseShippingAmount > 0) {
            // collect() relates the amount to the order's shipping including tax in this case
            $baseShippingAmount *= (float)$order->getBaseShippingInclTax() / $orderBaseShippingAmount;
        }

        return $baseShippingAmount;
    }

    /**
     * Returns whether the user specified a shipping amount that already includes tax
     *
     * @param int $storeId
     * @return bool
     */
    private function isShippingInclTax(int $storeId): bool
    {
        return (bool)$this->taxConfig->displaySalesShippingInclTax($storeId);
    }
}
