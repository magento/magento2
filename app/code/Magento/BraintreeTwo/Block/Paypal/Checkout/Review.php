<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Block\Paypal\Checkout;

use Magento\Paypal\Block\Express;

/**
 * Class Review
 */
class Review extends Express\Review
{
    /**
     * Controller path
     *
     * @var string
     */
    protected $_controllerPath = 'braintreetwo/paypal';

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
