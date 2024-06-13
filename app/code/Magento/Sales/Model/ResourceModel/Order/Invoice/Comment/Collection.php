<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Comment;

use Magento\Sales\Api\Data\InvoiceCommentSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Comment\Collection\AbstractCollection;

/**
 * Flat sales order invoice comment collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection implements InvoiceCommentSearchResultInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_comment_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_invoice_comment_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\Sales\Model\Order\Invoice\Comment::class,
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment::class
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
        return $this->setParentFilter($invoiceId);
    }
}
