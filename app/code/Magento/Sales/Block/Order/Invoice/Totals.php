<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\Invoice;

use Magento\Sales\Model\Order;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @var Order|null
     */
    protected $_invoice = null;

    /**
     * @return Order
     */
    public function getInvoice()
    {
        if ($this->_invoice === null) {
            if ($this->hasData('invoice')) {
                $this->_invoice = $this->_getData('invoice');
            } elseif ($this->_coreRegistry->registry('current_invoice')) {
                $this->_invoice = $this->_coreRegistry->registry('current_invoice');
            } elseif ($this->getParentBlock()->getInvoice()) {
                $this->_invoice = $this->getParentBlock()->getInvoice();
            }
        }
        return $this->_invoice;
    }

    /**
     * @param Order $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->_invoice = $invoice;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource()
    {
        return $this->getInvoice();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->removeTotal('base_grandtotal');
        return $this;
    }
}
