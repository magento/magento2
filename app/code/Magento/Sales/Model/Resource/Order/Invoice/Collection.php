<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Invoice;

use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Model\Resource\Order\Collection\AbstractCollection;

/**
 * Flat sales order invoice collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection implements InvoiceSearchResultInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_invoice_collection';

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'order_id';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Invoice', 'Magento\Sales\Model\Resource\Order\Invoice');
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
    }
}
