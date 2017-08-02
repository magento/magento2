<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice\Create;

/**
 * Adminhtml invoice create form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Invoice
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getInvoice();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     * @since 2.0.0
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Get save url
     *
     * @return string
     * @since 2.0.0
     */
    public function getSaveUrl()
    {
        return $this->getUrl('sales/*/save', ['order_id' => $this->getInvoice()->getOrderId()]);
    }

    /**
     * Check shipment availability for current invoice
     *
     * @return bool
     * @since 2.0.0
     */
    public function canCreateShipment()
    {
        foreach ($this->getInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->getQtyToShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check invoice shipment type mismatch
     *
     * @return bool
     * @since 2.0.0
     */
    public function hasInvoiceShipmentTypeMismatch()
    {
        foreach ($this->getInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->isChildrenCalculated() && !$item->getOrderItem()->isShipSeparately()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check shipment availability for partially item
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShipPartiallyItem()
    {
        $value = $this->getOrder()->getCanShipPartiallyItem();
        if ($value !== null && !$value) {
            return false;
        }
        return true;
    }

    /**
     * Return forced creating of shipment flag
     *
     * @return int
     * @since 2.0.0
     */
    public function getForcedShipmentCreate()
    {
        return (int)$this->getOrder()->getForcedShipmentWithInvoice();
    }
}
