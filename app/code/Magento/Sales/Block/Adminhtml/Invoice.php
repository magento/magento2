<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales invoices block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Invoice extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_invoice';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Invoices');
        parent::_construct();
        $this->buttonList->remove('add');
    }

    /**
     * Get payment info html
     *
     * @return string
     * @since 2.0.0
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }
}
