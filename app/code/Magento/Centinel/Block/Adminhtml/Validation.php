<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml sales order create validation card block
 */
namespace Magento\Centinel\Block\Adminhtml;

class Validation extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_validation_card');
    }

    /**
     * Return text for block`s header
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('3D Secure Card Validation');
    }

    /**
     * Return css class name for header block
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-payment-method';
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $payment = $this->getQuote()->getPayment();
        if (!$payment->getMethod() ||
            $payment->getMethodInstance()->getIsDummy() ||
            !$payment->getMethodInstance()->getIsCentinelValidationEnabled()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
