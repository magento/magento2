<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Customer\Account;

use Magento\Customer\Block\Account\SortLink;
use Magento\Paypal\Model\Config as PaypalConfig;

/**
 *  BillingAgreementsLink
 */
class BillingAgreementsLink extends SortLink
{
    /**
     * System Configuration path for Required PayPal Settings -> Enable this Solution
     */
    const XML_PATH_PAYPAL_PAYMENT_ENABLED = 'payment/paypal_express/active';

    /**
     * System Configuration path for Billing Agreement Signup
     */
    const XML_PATH_BILLING_AGREEMENT_SIGNUP = 'payment/paypal_express/allow_ba_signup';

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $moduleEnabled = $this->_scopeConfig->getValue(self::XML_PATH_PAYPAL_PAYMENT_ENABLED);
        $baSignupType = $this->_scopeConfig->getValue(self::XML_PATH_BILLING_AGREEMENT_SIGNUP);

        if (
            !$moduleEnabled ||
            $baSignupType == PaypalConfig::EC_BA_SIGNUP_NEVER
        ) {
            return '';
        }

        return parent::_toHtml();
    }
}
