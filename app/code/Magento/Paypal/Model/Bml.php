<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

class Bml extends Express
{
    /**
     * Payment method code
     * @var string
     */
    protected $_code  = Config::METHOD_WPP_BML;

    /**
     * Checkout payment form
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Bml\Form';

    /**
     * Checkout redirect URL getter for onepage checkout
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/bml/start');
    }
}
