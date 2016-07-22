<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface InvoiceValidatorInterface
 *
 */
class InvoiceValidator implements InvoiceValidatorInterface
{
    /**
     * @var OrderValidatorInterface
     */
    private $orderValidator;

    /**
     * InvoiceValidator constructor.
     * @param OrderValidatorInterface $orderValidator
     */
    public function __construct(OrderValidatorInterface $orderValidator)
    {
        $this->orderValidator = $orderValidator;
    }

    /**
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @return array
     */
    public function validate(InvoiceInterface $invoice, OrderInterface $order)
    {
        $messages = $this->checkQtyAvailability($invoice, $order);
        if (!$invoice->getTotalQty()) {
            $messages[] = __('You can\'t create an invoice without products.');
        }
        if (!$this->orderValidator->canInvoice($order)) {
            $messages[] = __('The order does not allow an invoice to be created.');
        }
        return $messages;
    }

    /**
     * Check qty availability
     *
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @return array
     */
    private function checkQtyAvailability(InvoiceInterface $invoice, OrderInterface $order)
    {
        $messages = [];
        $qtys = [];
        /** @var InvoiceItemInterface $item */
        foreach ($invoice->getItems() as $item) {
            $qtys[$item->getOrderItemId()] = $item->getQty();
        }

        if ($qtys) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($order->getItems() as $orderItem) {
                if (isset($qtys[$orderItem->getId()])
                    && $qtys[$orderItem->getId()] > $orderItem->getQtyToInvoice()
                    && !$orderItem->isDummy()
                ) {
                    $messages[] =  __('We found an invalid quantity to invoice item "%1".', $orderItem->getName());
                }
            }
        }
        return $messages;
    }
}
