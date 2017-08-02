<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Billing;

/**
 * Adminhtml sales order create payment method block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Method extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_billing_method');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Payment Method');
    }

    /**
     * Get header css class
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderCssClass()
    {
        return 'head-payment-method';
    }
}
