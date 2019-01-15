<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Item;

/**
 * Flat sales order invoice item collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_item_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_invoice_item_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Item::class
        );
    }

    /**
     * Set invoice filter
     *
     * @param int $invoiceId
     * @return $this
     */
    public function setInvoiceFilter($invoiceId)
    {
        $this->addFieldToFilter('parent_id', $invoiceId);
        return $this;
    }
}
