<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Helper\Shortcut;

class CheckoutValidator implements ValidatorInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $_paymentData;

    /**
     * @var ShortcutCheckoutValidator
     */
    private $_shortcutValidator;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Validator $shortcutValidator
     * @param \Magento\Payment\Helper\Data $paymentData
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        Validator $shortcutValidator,
        \Magento\Payment\Helper\Data $paymentData
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_paymentData = $paymentData;
        $this->_shortcutValidator = $shortcutValidator;
    }

    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     */
    public function validate($code, $isInCatalog)
    {
        return $this->_shortcutValidator->isContextAvailable($code, $isInCatalog)
            && $this->_shortcutValidator->isPriceOrSetAvailable($isInCatalog)
            && $this->isMethodQuoteAvailable($code, $isInCatalog)
            && $this->isQuoteSummaryValid($isInCatalog);
    }

    /**
     * Сhecks payment method and quote availability
     *
     * @param string $paymentCode
     * @param bool $isInCatalog
     * @return bool
     */
    public function isMethodQuoteAvailable($paymentCode, $isInCatalog)
    {
        $quote = $isInCatalog ? null : $this->_checkoutSession->getQuote();
        // check payment method availability
        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->_paymentData->getMethodInstance($paymentCode);
        if (!$methodInstance->isAvailable($quote)) {
            return false;
        }
        return true;
    }

    /**
     *  Validates minimum quote amount and zero grand total
     *
     * @param bool $isInCatalog
     * @return bool
     */
    public function isQuoteSummaryValid($isInCatalog)
    {
        $quote = $isInCatalog ? null : $this->_checkoutSession->getQuote();
        // validate minimum quote amount and validate quote for zero grandtotal
        if (null !== $quote && (!$quote->validateMinimumAmount() || !$quote->getGrandTotal())) {
            return false;
        }
        return true;
    }
}
