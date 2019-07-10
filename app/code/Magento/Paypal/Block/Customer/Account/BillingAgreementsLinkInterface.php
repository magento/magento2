<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Customer\Account;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * BillingAgreementsLinkInterface
 * @api
 */
interface BillingAgreementsLinkInterface extends SortLinkInterface
{
    /**
     * System Configuration path for Required PayPal Settings -> Enable this Solution
     */
    const XML_PATH_PAYPAL_PAYMENT_ENABLED = 'payment/paypal_express/active';

    /**
     * System Configuration path for Billing Agreement Signup
     */
    const XML_PATH_BILLING_AGREEMENT_SIGNUP = 'payment/paypal_express/allow_ba_signup';
}
