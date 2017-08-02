<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payment;

/**
 * PayPal common payment info block
 * Uses default templates
 * @since 2.0.0
 */
class Info extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @var \Magento\Paypal\Model\InfoFactory
     * @since 2.0.0
     */
    protected $_paypalInfoFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Paypal\Model\InfoFactory $paypalInfoFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Paypal\Model\InfoFactory $paypalInfoFactory,
        array $data = []
    ) {
        $this->_paypalInfoFactory = $paypalInfoFactory;
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * Don't show CC type for non-CC methods
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCcTypeName()
    {
        if (\Magento\Paypal\Model\Config::getIsCreditCardMethod($this->getInfo()->getMethod())) {
            return parent::getCcTypeName();
        }
    }

    /**
     * Prepare PayPal-specific payment information
     *
     * @param \Magento\Framework\DataObject|array|null $transport
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $paypalInfo = $this->_paypalInfoFactory->create();
        if ($this->getIsSecureMode()) {
            $info = $paypalInfo->getPublicPaymentInfo($payment, true);
        } else {
            $info = $paypalInfo->getPaymentInfo($payment, true);
        }
        return $transport->addData($info);
    }
}
