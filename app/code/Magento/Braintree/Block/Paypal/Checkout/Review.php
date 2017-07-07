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
 */
class Review extends Express\Review
{
    /**
     * Controller path
     *
     * @var string
     */
    protected $_controllerPath = 'braintree/paypal';

    /**
     * Does not allow editing payment information as customer has gone through paypal flow already
     *
     * @return null
     * @codeCoverageIgnore
     */
    public function getEditUrl()
    {
        return null;
    }
}
