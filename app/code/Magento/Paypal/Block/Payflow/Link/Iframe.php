<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payflow\Link;

/**
 * Payflow link iframe block
 *
 * @api
 * @since 2.0.0
 */
class Iframe extends \Magento\Paypal\Block\Iframe
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $_paymentData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Paypal\Helper\Hss $hssHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Paypal\Helper\Hss $hssHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Payment\Helper\Data $paymentData,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        parent::__construct($context, $orderFactory, $checkoutSession, $hssHelper, $readFactory, $reader, $data);
    }

    /**
     * Set payment method code
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_paymentMethodCode = \Magento\Paypal\Model\Config::METHOD_PAYFLOWLINK;
    }

    /**
     * Get frame action URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getFrameActionUrl()
    {
        return $this->getUrl('paypal/payflow/form', ['_secure' => true]);
    }

    /**
     * Get secure token
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecureToken()
    {
        return $this->_getOrder()->getPayment()->getAdditionalInformation('secure_token');
    }

    /**
     * Get secure token ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getSecureTokenId()
    {
        return $this->_getOrder()->getPayment()->getAdditionalInformation('secure_token_id');
    }

    /**
     * Get payflow transaction URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getTransactionUrl()
    {
        $cgiUrl = 'cgi_url';
        if ($this->isTestMode()) {
            $cgiUrl = 'cgi_url_test_mode';
        }
        return $this->_paymentData->getMethodInstance($this->_paymentMethodCode)->getConfigData($cgiUrl);
    }

    /**
     * Check sandbox mode
     *
     * @return bool
     * @since 2.0.0
     */
    public function isTestMode()
    {
        $mode = $this->_paymentData->getMethodInstance($this->_paymentMethodCode)->getConfigData('sandbox_flag');
        return (bool)$mode;
    }
}
