<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Flat sales order invoice collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends AbstractCollection implements InvoiceSearchResultInterface
{
    /**
     * Id field name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_invoice_collection';

    /**
     * Event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'order_invoice_collection';

    /**
     * Order field for setOrderFilter
     *
     * @var string
     * @since 2.0.0
     */
    protected $_orderField = 'order_id';

    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Invoice::class,
            \Magento\Sales\Model\ResourceModel\Order\Invoice::class
        );
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return void
     * @since 2.0.0
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
    }
}
