<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Helper\Shortcut;

/**
 * Class \Magento\Paypal\Helper\Shortcut\CheckoutValidator
 *
 * @since 2.0.0
 */
class CheckoutValidator implements ValidatorInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    private $_paymentData;

    /**
     * @var ShortcutCheckoutValidator
     * @since 2.0.0
     */
    private $_shortcutValidator;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Validator $shortcutValidator
     * @param \Magento\Payment\Helper\Data $paymentData
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function validate($code, $isInCatalog)
    {
        return $this->_shortcutValidator->isContextAvailable($code, $isInCatalog)
            && $this->_shortcutValidator->isPriceOrSetAvailable($isInCatalog)
            && $this->isMethodQuoteAvailable($code, $isInCatalog)
            && $this->isQuoteSummaryValid($isInCatalog);
    }

    /**
     * Checks payment method and quote availability
     *
     * @param string $paymentCode
     * @param bool $isInCatalog
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
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
