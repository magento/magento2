<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Block\Adminhtml\Order\View\Info;

use Magento\Authorizenet\Model\Directpost;

/**
 * Fraud information block for Authorize.net payment method
 *
 * @api
 * @since 100.0.2
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class FraudDetails extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return payment method model
     *
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    /**
     * Produce and return the block's HTML output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->getPayment()->getMethod() === Directpost::METHOD_CODE) ? parent::_toHtml() : '';
    }
}
