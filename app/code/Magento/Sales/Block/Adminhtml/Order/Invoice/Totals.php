<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice;

use Magento\Sales\Model\Order\Invoice;

/**
 * Adminhtml order invoice totals block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    /**
     * Order invoice
     *
     * @var Invoice|null
     * @since 2.0.0
     */
    protected $_invoice = null;

    /**
     * Get invoice
     *
     * @return Invoice|null
     * @since 2.0.0
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
     * Get source
     *
     * @return Invoice|null
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->getInvoice();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        return $this;
    }
}
