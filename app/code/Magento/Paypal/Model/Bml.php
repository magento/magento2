<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
    protected $_formBlockType = \Magento\Paypal\Block\Bml\Form::class;

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
