<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Multishipping checkout payment information data
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Multishipping\Block\Checkout\Payment;

/**
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Payment\Block\Info\AbstractContainer
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        array $data = []
    ) {
        $this->_multishipping = $multishipping;
        parent::__construct($context, $paymentData, $data);
    }

    /**
     * Retrieve payment info model
     *
     * @return \Magento\Payment\Model\Info
     */
    public function getPaymentInfo()
    {
        return $this->_multishipping->getQuote()->getPayment();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $block = $this->getChildBlock($this->_getInfoBlockName());
        if ($block) {
            $html = $block->toHtml();
        }
        return $html;
    }
}
