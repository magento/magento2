<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

/**
 * Invoice pay operation.
 */
class PayOperation
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\Context $context
    ) {
        $this->eventManager = $context->getEventDispatcher();
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @param bool $capture
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function execute(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\InvoiceInterface $invoice,
        $capture
    ) {
        $this->calculateOrderItemsTotals(
            $invoice->getItems()
        );

        if ($this->canCapture($order, $invoice)) {
            if ($capture) {
                $invoice->capture();
            } else {
                $invoice->setCanVoidFlag(false);

                $invoice->pay();
            }
        } elseif (!$order->getPayment()->getMethodInstance()->isGateway() || !$capture) {
            if (!$order->getPayment()->getIsTransactionPending()) {
                $invoice->setCanVoidFlag(false);

                $invoice->pay();
            }
        }

        $this->calculateOrderTotals($order, $invoice);

        if (null === $invoice->getState()) {
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);
        }

        $this->eventManager->dispatch(
            'sales_order_invoice_register',
            ['invoice' => $invoice, 'order' => $order]
        );

        return $order;
    }

    /**
     * Calculates totals of Order Items according to given Invoice Items.
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface[] $items
     *
     * @return void
     */
    private function calculateOrderItemsTotals($items)
    {
        foreach ($items as $item) {
            if ($item->isDeleted()) {
                continue;
            }

            if ($item->getQty() > 0) {
                $item->register();
            } else {
                $item->isDeleted(true);
            }
        }
    }

    /**
     * Checks Invoice capture action availability.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     *
     * @return bool
     */
    private function canCapture(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\InvoiceInterface $invoice
    ) {
        return $invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_CANCELED &&
        $invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID &&
        $order->getPayment()->canCapture();
    }

    /**
     * Calculates Order totals according to given Invoice.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     *
     * @return void
     */
    private function calculateOrderTotals(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\InvoiceInterface $invoice
    ) {
        $order->setTotalInvoiced(
            $order->getTotalInvoiced() + $invoice->getGrandTotal()
        );
        $order->setBaseTotalInvoiced(
            $order->getBaseTotalInvoiced() + $invoice->getBaseGrandTotal()
        );

        $order->setSubtotalInvoiced(
            $order->getSubtotalInvoiced() + $invoice->getSubtotal()
        );
        $order->setBaseSubtotalInvoiced(
            $order->getBaseSubtotalInvoiced() + $invoice->getBaseSubtotal()
        );

        $order->setTaxInvoiced(
            $order->getTaxInvoiced() + $invoice->getTaxAmount()
        );
        $order->setBaseTaxInvoiced(
            $order->getBaseTaxInvoiced() + $invoice->getBaseTaxAmount()
        );

        $order->setDiscountTaxCompensationInvoiced(
            $order->getDiscountTaxCompensationInvoiced() + $invoice->getDiscountTaxCompensationAmount()
        );
        $order->setBaseDiscountTaxCompensationInvoiced(
            $order->getBaseDiscountTaxCompensationInvoiced() + $invoice->getBaseDiscountTaxCompensationAmount()
        );

        $order->setShippingTaxInvoiced(
            $order->getShippingTaxInvoiced() + $invoice->getShippingTaxAmount()
        );
        $order->setBaseShippingTaxInvoiced(
            $order->getBaseShippingTaxInvoiced() + $invoice->getBaseShippingTaxAmount()
        );

        $order->setShippingInvoiced(
            $order->getShippingInvoiced() + $invoice->getShippingAmount()
        );
        $order->setBaseShippingInvoiced(
            $order->getBaseShippingInvoiced() + $invoice->getBaseShippingAmount()
        );

        $order->setDiscountInvoiced(
            $order->getDiscountInvoiced() + $invoice->getDiscountAmount()
        );
        $order->setBaseDiscountInvoiced(
            $order->getBaseDiscountInvoiced() + $invoice->getBaseDiscountAmount()
        );

        $order->setBaseTotalInvoicedCost(
            $order->getBaseTotalInvoicedCost() + $invoice->getBaseCost()
        );
    }
}
