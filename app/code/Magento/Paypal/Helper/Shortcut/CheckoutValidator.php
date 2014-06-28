<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        if (!$methodInstance || !$methodInstance->isAvailable($quote)) {
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
        if (null !== $quote && (!$quote->validateMinimumAmount() ||
            !$quote->getGrandTotal() && !$quote->hasNominalItems())
        ) {
            return false;
        }
        return true;
    }
}
