<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Block\Checkout;

/**
 * Braintree PayPal shortcut checkout block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Review extends \Magento\Paypal\Block\Express\Review
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
