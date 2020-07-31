<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Paypal\Checkout;

use Magento\Paypal\Block\Express;

/**
 * Class Review
 *
 * @api
 * @since 100.1.0
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class Review extends Express\Review
{
    /**
     * Controller path
     *
     * @var string
     * @since 100.1.0
     */
    protected $_controllerPath = 'braintree/paypal';

    /**
     * Does not allow editing payment information as customer has gone through paypal flow already
     *
     * @return null
     * @codeCoverageIgnore
     * @since 100.1.0
     */
    public function getEditUrl()
    {
        return null;
    }
}
