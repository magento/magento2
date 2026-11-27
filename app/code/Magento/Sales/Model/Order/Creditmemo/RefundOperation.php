<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Item as OrderItem;

class RefundOperation
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->eventManager = $context->getEventDispatcher();
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param bool $online
     * @return OrderInterface
     */
    public function execute(CreditmemoInterface $creditmemo, OrderInterface $order, $online = false)
    {
        if ($creditmemo->getState() == Creditmemo::STATE_REFUNDED
            && $creditmemo->getOrderId() == $order->getEntityId()
        ) {
            foreach ($creditmemo->getItems() as $item) {
                if ($item->isDeleted()) {
                    continue;
                }
                $item->setCreditMemo($creditmemo);
                if ($item->getQty() > 0) {
                    $item->register();
                } else {
                    $item->isDeleted(true);
                }
            }
            $this->updateBundleItemsQtyRefunded($order);

            $baseOrderRefund = $this->priceCurrency->round(
                $order->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
            );
            $orderRefund = $this->priceCurrency->round(
                $order->getTotalRefunded() + $creditmemo->getGrandTotal()
            );
            $order->setBaseTotalRefunded($baseOrderRefund);
            $order->setTotalRefunded($orderRefund);

            $order->setBaseSubtotalRefunded($order->getBaseSubtotalRefunded() + $creditmemo->getBaseSubtotal());
            $order->setSubtotalRefunded($order->getSubtotalRefunded() + $creditmemo->getSubtotal());

            $order->setBaseTaxRefunded($order->getBaseTaxRefunded() + $creditmemo->getBaseTaxAmount());
            $order->setTaxRefunded($order->getTaxRefunded() + $creditmemo->getTaxAmount());
            $order->setBaseDiscountTaxCompensationRefunded(
                $order->getBaseDiscountTaxCompensationRefunded() + $creditmemo->getBaseDiscountTaxCompensationAmount()
            );
            $order->setDiscountTaxCompensationRefunded(
                $order->getDiscountTaxCompensationRefunded() + $creditmemo->getDiscountTaxCompensationAmount()
            );

            $order->setBaseShippingRefunded($order->getBaseShippingRefunded() + $creditmemo->getBaseShippingAmount());
            $order->setShippingRefunded($order->getShippingRefunded() + $creditmemo->getShippingAmount());

            $order->setBaseShippingTaxRefunded(
                $order->getBaseShippingTaxRefunded() + $creditmemo->getBaseShippingTaxAmount()
            );
            $order->setShippingTaxRefunded($order->getShippingTaxRefunded() + $creditmemo->getShippingTaxAmount());

            $order->setAdjustmentPositive($order->getAdjustmentPositive() + $creditmemo->getAdjustmentPositive());
            $order->setBaseAdjustmentPositive(
                $order->getBaseAdjustmentPositive() + $creditmemo->getBaseAdjustmentPositive()
            );

            $order->setAdjustmentNegative($order->getAdjustmentNegative() + $creditmemo->getAdjustmentNegative());
            $order->setBaseAdjustmentNegative(
                $order->getBaseAdjustmentNegative() + $creditmemo->getBaseAdjustmentNegative()
            );

            $order->setDiscountRefunded($order->getDiscountRefunded() + $creditmemo->getDiscountAmount());
            $order->setBaseDiscountRefunded($order->getBaseDiscountRefunded() + $creditmemo->getBaseDiscountAmount());

            if ($online) {
                $order->setTotalOnlineRefunded($order->getTotalOnlineRefunded() + $creditmemo->getGrandTotal());
                $order->setBaseTotalOnlineRefunded(
                    $order->getBaseTotalOnlineRefunded() + $creditmemo->getBaseGrandTotal()
                );
            } else {
                $order->setTotalOfflineRefunded($order->getTotalOfflineRefunded() + $creditmemo->getGrandTotal());
                $order->setBaseTotalOfflineRefunded(
                    $order->getBaseTotalOfflineRefunded() + $creditmemo->getBaseGrandTotal()
                );
            }

            $order->setBaseTotalInvoicedCost(
                $order->getBaseTotalInvoicedCost() - $creditmemo->getBaseCost()
            );

            $creditmemo->setDoTransaction(!$creditmemo->getPaymentRefundDisallowed() && $online);
            $order->getPayment()->refund($creditmemo);

            $this->eventManager->dispatch('sales_order_creditmemo_refund', ['creditmemo' => $creditmemo]);
        }

        return $order;
    }

    /**
     * Set the refunded quantity of dynamic-price bundle items from their refunded children
     *
     * Each credit memo line adds its own quantity to the order item, and the bundle line of a dynamic-price
     * bundle always carries the invoiced bundle quantity, so a partial refund of its items would count every
     * bundle as refunded. Once all lines are registered, each such bundle item is set to the number of complete
     * bundles its items' refunded quantities add up to, including earlier credit memos.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function updateBundleItemsQtyRefunded(OrderInterface $order): void
    {
        foreach ($order->getItems() ?? [] as $orderItem) {
            if (!$this->isDynamicBundleItem($orderItem)) {
                continue;
            }
            $completeBundles = $this->getCompleteBundlesRefunded($orderItem);
            if ($completeBundles !== null) {
                $orderItem->setQtyRefunded(min($completeBundles, (float)$orderItem->getQtyInvoiced()));
            }
        }
    }

    /**
     * Check whether an order item is the parent item of a dynamic-price bundle
     *
     * @param mixed $orderItem
     * @return bool
     */
    private function isDynamicBundleItem($orderItem): bool
    {
        return $orderItem instanceof OrderItem
            && $orderItem->isDummy()
            && $orderItem->getProductType() === ProductType::TYPE_BUNDLE
            && $orderItem->getChildrenItems();
    }

    /**
     * Get the number of complete bundles that the refunded quantities of a bundle's items add up to
     *
     * @param OrderItem $bundleItem
     * @return float|null null when no item has a quantity per bundle
     */
    private function getCompleteBundlesRefunded(OrderItem $bundleItem): ?float
    {
        $bundleQty = (float)$bundleItem->getQtyOrdered();
        if ($bundleQty <= 0) {
            return null;
        }
        $completeBundles = null;
        foreach ($bundleItem->getChildrenItems() as $childItem) {
            $qtyPerBundle = (float)$childItem->getQtyOrdered() / $bundleQty;
            if ($qtyPerBundle <= 0) {
                continue;
            }
            $childBundles = floor(round((float)$childItem->getQtyRefunded() / $qtyPerBundle, 4));
            $completeBundles = $completeBundles === null ? $childBundles : min($completeBundles, $childBundles);
        }

        return $completeBundles;
    }
}
