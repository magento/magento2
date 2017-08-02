<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice\Grid;

/**
 * Sales invoices statuses option array
 * @since 2.0.0
 */
class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     * @since 2.0.0
     */
    protected $invoiceRepository;

    /**
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @since 2.0.0
     */
    public function __construct(\Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Return option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->invoiceRepository->create()->getStates();
    }
}
