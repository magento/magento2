<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Block\Adminhtml\Order\View\Info;

use Magento\Authorizenet\Model\Directpost;

/**
 * @api
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
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->getPayment()->getMethod() === Directpost::METHOD_CODE) ? parent::_toHtml() : '';
    }
}
