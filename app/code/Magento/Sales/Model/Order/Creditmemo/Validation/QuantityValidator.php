<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Validation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Class QuantityValidator
 */
class QuantityValidator implements ValidatorInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * InvoiceValidator constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param CreditmemoInterface $entity
     * @return array
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     */
    public function validate($entity)
    {
        if ($entity->getOrderId() === null) {
            return [__('Order Id is required for shipment document')];
        }

        if (empty($entity->getItems())) {
            return [__('You can\'t create a creditmemo without products.')];
        }
        $messages = [];

        $order = $this->orderRepository->get($entity->getOrderId());
        $orderItemsById = $this->getOrderItems($order);
        $invoiceQtysRefundLimits = $this->getInvoiceQtysRefundLimits($entity, $order);

        $totalQuantity = 0;
        foreach ($entity->getItems() as $item) {
            if (!isset($orderItemsById[$item->getOrderItemId()])) {
                $messages[] = __(
                    'The creditmemo contains product SKU "%1" that is not part of the original order.',
                    $item->getSku()
                );
                continue;
            }
            $orderItem = $orderItemsById[$item->getOrderItemId()];

            if (
                !$this->canRefundItem($orderItem, $item->getQty(), $invoiceQtysRefundLimits) ||
                !$this->isQtyAvailable($orderItem, $item->getQty())
            ) {
                $messages[] =__(
                    'The quantity to creditmemo must not be greater than the unrefunded quantity'
                    . ' for product SKU "%1".',
                    $orderItem->getSku()
                );
            } else {
                $totalQuantity += $item->getQty();
            }
        }
        if ($totalQuantity <= 0) {
            $messages[] = __('You can\'t create a creditmemo without products.');
        }

        return $messages;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @return array
     */
    private function getInvoiceQtysRefundLimits(CreditmemoInterface $creditmemo, OrderInterface $order)
    {
        $invoiceQtysRefundLimits = [];
        if ($creditmemo->getInvoiceId() !== null) {
            $invoiceQtysRefunded = [];
            $invoice = $this->invoiceRepository->get($creditmemo->getInvoiceId());
            foreach ($order->getCreditmemosCollection() as $createdCreditmemo) {
                if ($createdCreditmemo->getState() != Creditmemo::STATE_CANCELED &&
                    $createdCreditmemo->getInvoiceId() == $invoice->getId()
                ) {
                    foreach ($createdCreditmemo->getAllItems() as $createdCreditmemoItem) {
                        $orderItemId = $createdCreditmemoItem->getOrderItem()->getId();
                        if (isset($invoiceQtysRefunded[$orderItemId])) {
                            $invoiceQtysRefunded[$orderItemId] += $createdCreditmemoItem->getQty();
                        } else {
                            $invoiceQtysRefunded[$orderItemId] = $createdCreditmemoItem->getQty();
                        }
                    }
                }
            }

            foreach ($invoice->getItems() as $invoiceItem) {
                $invoiceQtyCanBeRefunded = $invoiceItem->getQty();
                $orderItemId = $invoiceItem->getOrderItem()->getId();
                if (isset($invoiceQtysRefunded[$orderItemId])) {
                    $invoiceQtyCanBeRefunded = $invoiceQtyCanBeRefunded - $invoiceQtysRefunded[$orderItemId];
                }
                $invoiceQtysRefundLimits[$orderItemId] = $invoiceQtyCanBeRefunded;
            }
        }

        return $invoiceQtysRefundLimits;
    }

    /**
     * @param OrderInterface $order
     * @return OrderItemInterface[]
     */
    private function getOrderItems(OrderInterface $order)
    {
        $orderItemsById = [];
        foreach ($order->getItems() as $item) {
            $orderItemsById[$item->getItemId()] = $item;
        }

        return $orderItemsById;
    }

    /**
     * @param Item $orderItem
     * @param int $qty
     * @return bool
     */
    private function isQtyAvailable(Item $orderItem, $qty)
    {
        return $qty <= $orderItem->getQtyToRefund() || $orderItem->isDummy();
    }

    /**
     * Check if order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param double $qty
     * @param array $invoiceQtysRefundLimits
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function canRefundItem(\Magento\Sales\Model\Order\Item $item, $qty, $invoiceQtysRefundLimits)
    {
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if ($qty === null) {
                        if ($this->canRefundNoDummyItem($child, $invoiceQtysRefundLimits)) {
                            return true;
                        }
                    } else {
                        if ($qty > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if ($qty === null) {
                    return $this->canRefundNoDummyItem($parent, $invoiceQtysRefundLimits);
                } else {
                    return $qty > 0;
                }
            }
        } else {
            return $this->canRefundNoDummyItem($item, $invoiceQtysRefundLimits);
        }
    }

    /**
     * Check if no dummy order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    private function canRefundNoDummyItem($item, $invoiceQtysRefundLimits = [])
    {
        if ($item->getQtyToRefund() < 0) {
            return false;
        }
        if (isset($invoiceQtysRefundLimits[$item->getId()])) {
            return $invoiceQtysRefundLimits[$item->getId()] > 0;
        }
        return true;
    }
}
