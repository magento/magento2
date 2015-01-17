<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Invoice\Grid;

/**
 * Sales invoices statuses option array
 */
class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
     */
    public function __construct(\Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory)
    {
        $this->invoiceFactory = $invoiceFactory;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->invoiceFactory->create()->getStates();
    }
}
