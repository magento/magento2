<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

/**
 * Class \Magento\Paypal\Model\Bml
 *
 * @since 2.0.0
 */
class Bml extends Express
{
    /**
     * Payment method code
     * @var string
     * @since 2.0.0
     */
    protected $_code  = Config::METHOD_WPP_BML;

    /**
     * Checkout payment form
     * @var string
     * @since 2.0.0
     */
    protected $_formBlockType = \Magento\Paypal\Block\Bml\Form::class;

    /**
     * Checkout redirect URL getter for onepage checkout
     *
     * @return string
     * @since 2.0.0
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/bml/start');
    }
}
