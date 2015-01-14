<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

/**
 * Quote submit service model
 */
class Order
{
    /**
     * Order object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Quote convert object
     *
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertor;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->_order = $order;
        $this->_convertor = $convertOrderFactory->create();
        $this->_taxConfig = $taxConfig;
    }

    /**
     * Quote convertor declaration
     *
     * @param \Magento\Sales\Model\Convert\Order $convertor
     * @return $this
     */
    public function setConvertor(\Magento\Sales\Model\Convert\Order $convertor)
    {
        $this->_convertor = $convertor;
        return $this;
    }

    /**
     * Get assigned order object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Prepare order invoice based on order data and requested items qtys. If $qtys is not empty - the function will
     * prepare only specified items, otherwise all containing in the order.
     *
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function prepareInvoice($qtys = [])
    {
        $invoice = $this->_convertor->toInvoice($this->_order);
        $totalQty = 0;
        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canInvoiceItem($orderItem, [])) {
                continue;
            }
            $item = $this->_convertor->itemToInvoiceItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (!empty($qtys)) {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (double)$qtys[$orderItem->getId()];
                }
            } else {
                $qty = $orderItem->getQtyToInvoice();
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $this->_order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }

    /**
     * Prepare order shipment based on order items and requested items qty
     *
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function prepareShipment($qtys = [])
    {
        $totalQty = 0;
        $shipment = $this->_convertor->toShipment($this->_order);
        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canShipItem($orderItem, $qtys)) {
                continue;
            }

            $item = $this->_convertor->itemToShipmentItem($orderItem);

            if ($orderItem->isDummy(true)) {
                $qty = 0;
                if (isset($qtys[$orderItem->getParentItemId()])) {
                    $productOptions = $orderItem->getProductOptions();
                    if (isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = unserialize($productOptions['bundle_selection_attributes']);

                        if ($bundleSelectionAttributes) {
                            $qty = $bundleSelectionAttributes['qty'] * $qtys[$orderItem->getParentItemId()];
                            $qty = min($qty, $orderItem->getSimpleQtyToShip());

                            $item->setQty($qty);
                            $shipment->addItem($item);
                            continue;
                        } else {
                            $qty = 1;
                        }
                    }
                } else {
                    $qty = 1;
                }
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = min($qtys[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $shipment->addItem($item);
        }

        $shipment->setTotalQty($totalQty);
        return $shipment;
    }

    /**
     * Prepare order creditmemo based on order items and requested params
     *
     * @param array $data
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function prepareCreditmemo($data = [])
    {
        $totalQty = 0;
        $creditmemo = $this->_convertor->toCreditmemo($this->_order);
        $qtys = isset($data['qtys']) ? $data['qtys'] : [];

        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canRefundItem($orderItem, $qtys)) {
                continue;
            }

            $item = $this->_convertor->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = 1;
                $orderItem->setLockedDoShip(true);
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (double)$qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->_initCreditmemoData($creditmemo, $data);

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Prepare order creditmemo based on invoice items and requested requested params
     *
     * @param object $invoice
     * @param array $data
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function prepareInvoiceCreditmemo($invoice, $data = [])
    {
        $totalQty = 0;
        $qtys = isset($data['qtys']) ? $data['qtys'] : [];
        $creditmemo = $this->_convertor->toCreditmemo($this->_order);
        $creditmemo->setInvoice($invoice);

        $invoiceQtysRefunded = [];
        foreach ($invoice->getOrder()->getCreditmemosCollection() as $createdCreditmemo) {
            if ($createdCreditmemo->getState() != \Magento\Sales\Model\Order\Creditmemo::STATE_CANCELED &&
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

        $invoiceQtysRefundLimits = [];
        foreach ($invoice->getAllItems() as $invoiceItem) {
            $invoiceQtyCanBeRefunded = $invoiceItem->getQty();
            $orderItemId = $invoiceItem->getOrderItem()->getId();
            if (isset($invoiceQtysRefunded[$orderItemId])) {
                $invoiceQtyCanBeRefunded = $invoiceQtyCanBeRefunded - $invoiceQtysRefunded[$orderItemId];
            }
            $invoiceQtysRefundLimits[$orderItemId] = $invoiceQtyCanBeRefunded;
        }

        foreach ($invoice->getAllItems() as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();

            if (!$this->_canRefundItem($orderItem, $qtys, $invoiceQtysRefundLimits)) {
                continue;
            }

            $item = $this->_convertor->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = 1;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (double)$qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
                if (isset($invoiceQtysRefundLimits[$orderItem->getId()])) {
                    $qty = min($qty, $invoiceQtysRefundLimits[$orderItem->getId()]);
                }
            }
            $qty = min($qty, $invoiceItem->getQty());
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->_initCreditmemoData($creditmemo, $data);
        if (!isset($data['shipping_amount'])) {
            $order = $invoice->getOrder();
            $isShippingInclTax = $this->_taxConfig->displaySalesShippingInclTax($order->getStoreId());
            if ($isShippingInclTax) {
                $baseAllowedAmount = $order->getBaseShippingInclTax() -
                    $order->getBaseShippingRefunded() -
                    $order->getBaseShippingTaxRefunded();
            } else {
                $baseAllowedAmount = $order->getBaseShippingAmount() - $order->getBaseShippingRefunded();
                $baseAllowedAmount = min($baseAllowedAmount, $invoice->getBaseShippingAmount());
            }
            $creditmemo->setBaseShippingAmount($baseAllowedAmount);
        }

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Initialize creditmemo state based on requested parameters
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param array $data
     * @return void
     */
    protected function _initCreditmemoData($creditmemo, $data)
    {
        if (isset($data['shipping_amount'])) {
            $creditmemo->setBaseShippingAmount((double)$data['shipping_amount']);
        }

        if (isset($data['adjustment_positive'])) {
            $creditmemo->setAdjustmentPositive($data['adjustment_positive']);
        }

        if (isset($data['adjustment_negative'])) {
            $creditmemo->setAdjustmentNegative($data['adjustment_negative']);
        }
    }

    /**
     * Check if order item can be invoiced. Dummy item can be invoiced or with his children or
     * with parent item which is included to invoice
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $qtys
     * @return bool
     */
    protected function _canInvoiceItem($item, $qtys = [])
    {
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }

    /**
     * Check if order item can be shipped. Dummy item can be shipped or with his children or
     * with parent item which is included to shipment
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $qtys
     * @return bool
     */
    protected function _canShipItem($item, $qtys = [])
    {
        if ($item->getIsVirtual() || $item->getLockedDoShip()) {
            return false;
        }
        if ($item->isDummy(true)) {
            if ($item->getHasChildren()) {
                if ($item->isShipSeparately()) {
                    return true;
                }
                foreach ($item->getChildrenItems() as $child) {
                    if ($child->getIsVirtual()) {
                        continue;
                    }
                    if (empty($qtys)) {
                        if ($child->getQtyToShip() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToShip() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToShip() > 0;
        }
    }

    /**
     * Check if order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $qtys
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function _canRefundItem($item, $qtys = [], $invoiceQtysRefundLimits = [])
    {
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($this->_canRefundNoDummyItem($child, $invoiceQtysRefundLimits)) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $this->_canRefundNoDummyItem($parent, $invoiceQtysRefundLimits);
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $this->_canRefundNoDummyItem($item, $invoiceQtysRefundLimits);
        }
    }

    /**
     * Check if no dummy order item can be refunded
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function _canRefundNoDummyItem($item, $invoiceQtysRefundLimits = [])
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
