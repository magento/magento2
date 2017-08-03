<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Helper;

/**
 * Hosted Sole Solution helper
 * @since 2.0.0
 */
class Hss extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Hosted Sole Solution methods
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_hssMethods = [
        \Magento\Paypal\Model\Config::METHOD_HOSTEDPRO,
        \Magento\Paypal\Model\Config::METHOD_PAYFLOWLINK,
        \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED,
    ];

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Get template for button in order review page if HSS method was selected
     *
     * @param string $name template name
     * @return string
     * @since 2.0.0
     */
    public function getReviewButtonTemplate($name)
    {
        $quote = $this->_checkoutSession->getQuote();
        if ($quote) {
            $payment = $quote->getPayment();
            if ($payment && in_array($payment->getMethod(), $this->_hssMethods)) {
                return $name;
            }
        }
        return '';
    }

    /**
     * Get methods
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getHssMethods()
    {
        return $this->_hssMethods;
    }
}
