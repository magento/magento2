<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order view items block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Order\Invoice;

/**
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @param object $invoice
     * @return string
     * @since 2.0.0
     */
    public function getPrintInvoiceUrl($invoice)
    {
        return $this->getUrl('*/*/printInvoice', ['invoice_id' => $invoice->getId()]);
    }

    /**
     * @param object $order
     * @return string
     * @since 2.0.0
     */
    public function getPrintAllInvoicesUrl($order)
    {
        return $this->getUrl('*/*/printInvoice', ['order_id' => $order->getId()]);
    }

    /**
     * Get html of invoice totals block
     *
     * @param   \Magento\Sales\Model\Order\Invoice $invoice
     * @return  string
     * @since 2.0.0
     */
    public function getInvoiceTotalsHtml($invoice)
    {
        $html = '';
        $totals = $this->getChildBlock('invoice_totals');
        if ($totals) {
            $totals->setInvoice($invoice);
            $html = $totals->toHtml();
        }
        return $html;
    }

    /**
     * Get html of invoice comments block
     *
     * @param   \Magento\Sales\Model\Order\Invoice $invoice
     * @return  string
     * @since 2.0.0
     */
    public function getInvoiceCommentsHtml($invoice)
    {
        $html = '';
        $comments = $this->getChildBlock('invoice_comments');
        if ($comments) {
            $comments->setEntity($invoice)->setTitle(__('About Your Invoice'));
            $html = $comments->toHtml();
        }
        return $html;
    }
}
