<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice\View;

/**
 * Adminhtml sales item renderer
 *
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
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
     * Retrieve order url
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getInvoice()->getOrderId()]);
    }

    /**
     * Retrieve formatted price
     *
     * @param float $price
     * @return string
     * @since 2.0.0
     */
    public function formatPrice($price)
    {
        return $this->getInvoice()->getOrder()->formatPrice($price);
    }
}
