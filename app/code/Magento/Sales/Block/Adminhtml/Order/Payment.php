<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Payment\Model\Info;

/**
 * Adminhtml sales order payment information
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Payment extends \Magento\Backend\Block\Template
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $_paymentData = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid parent block for this block'));
        }
        $this->setPayment($this->getParentBlock()->getOrder()->getPayment());
        parent::_beforeToHtml();
    }

    /**
     * Set payment
     *
     * @param Info $payment
     * @return $this
     * @since 2.0.0
     */
    public function setPayment($payment)
    {
        $paymentInfoBlock = $this->_paymentData->getInfoBlock($payment, $this->getLayout());
        $this->setChild('info', $paymentInfoBlock);
        $this->setData('payment', $payment);
        return $this;
    }

    /**
     * Prepare html output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return $this->getChildHtml('info');
    }
}
