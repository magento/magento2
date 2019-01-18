<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Block\Adminhtml\Order\View\Info;

use Magento\Authorizenet\Model\Directpost;

/**
 * @api
 * @since 100.0.2
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method in July 2019
 */
class FraudDetails extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     * @deprecated
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @deprecated
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
     * @return \Magento\Sales\Model\Order\Payment
     * @deprecated
     */
    public function getPayment()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    /**
     * @return string
     * @deprecated
     */
    protected function _toHtml()
    {
        return ($this->getPayment()->getMethod() === Directpost::METHOD_CODE) ? parent::_toHtml() : '';
    }
}
