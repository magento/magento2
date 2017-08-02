<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Interface InvoiceValidatorInterface
 * @since 2.2.0
 */
class InvoiceQuantityValidator implements ValidatorInterface
{
    /**
     * @var OrderRepositoryInterface
     * @since 2.2.0
     */
    private $orderRepository;

    /**
     * InvoiceValidator constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @since 2.2.0
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function validate($invoice)
    {
        if ($invoice->getOrderId() === null) {
            return [__('Order Id is required for invoice document')];
        }
        $order = $this->orderRepository->get($invoice->getOrderId());
        return $this->checkQtyAvailability($invoice, $order);
    }

    /**
     * Check qty availability
     *
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @return array
     * @since 2.2.0
     */
    private function checkQtyAvailability(InvoiceInterface $invoice, OrderInterface $order)
    {
        $messages = [];
        $qtys = $this->getInvoiceQty($invoice);

        $totalQty = 0;
        if ($qtys) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($order->getItems() as $orderItem) {
                if (isset($qtys[$orderItem->getId()])) {
                    if ($qtys[$orderItem->getId()] > $orderItem->getQtyToInvoice() && !$orderItem->isDummy()) {
                        $messages[] = __(
                            'The quantity to invoice must not be greater than the uninvoiced quantity'
                            . ' for product SKU "%1".',
                            $orderItem->getSku()
                        );
                    }
                    $totalQty += $qtys[$orderItem->getId()];
                    unset($qtys[$orderItem->getId()]);
                }
            }
        }
        if ($qtys) {
            $messages[] = __('The invoice contains one or more items that are not part of the original order.');
        } elseif ($totalQty <= 0) {
            $messages[] = __('You can\'t create an invoice without products.');
        }
        return $messages;
    }

    /**
     * @param InvoiceInterface $invoice
     * @return array
     * @since 2.2.0
     */
    private function getInvoiceQty(InvoiceInterface $invoice)
    {
        $qtys = [];
        /** @var InvoiceItemInterface $item */
        foreach ($invoice->getItems() as $item) {
            $qtys[$item->getOrderItemId()] = $item->getQty();
        }
        return $qtys;
    }
}
