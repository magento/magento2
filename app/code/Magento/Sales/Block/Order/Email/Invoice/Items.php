<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Order\Email\Invoice;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Sales Order Email Invoice items
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
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
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     * @param InvoiceRepositoryInterface|null $invoiceRepository
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?OrderRepositoryInterface $orderRepository = null,
        ?InvoiceRepositoryInterface $invoiceRepository = null
    ) {
        $this->orderRepository =
            $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $this->invoiceRepository =
            $invoiceRepository ?: ObjectManager::getInstance()->get(InvoiceRepositoryInterface::class);

        parent::__construct($context, $data);
    }

    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     * @return void
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getInvoice());
    }

    /**
     * Returns order.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So order is loaded by order_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        $order = $this->getData('order');
        if ($order !== null) {
            return $order;
        }

        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }

    /**
     * Returns invoice.
     *
     * Custom email templates are only allowed to use scalar values for variable data.
     * So invoice is loaded by invoice_id, that is passed to block from email template.
     * For legacy custom email templates it can pass as an object.
     *
     * @return InvoiceInterface|null
     */
    public function getInvoice()
    {
        $invoice = $this->getData('invoice');
        if ($invoice !== null) {
            return $invoice;
        }

        $invoiceId = (int)$this->getData('invoice_id');
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $this->setData('invoice', $invoice);
        }

        return $this->getData('invoice');
    }
}
