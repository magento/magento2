<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\Payflow;

use Magento\Paypal\Model;

/**
 * Class \Magento\Paypal\Model\Payflow\Bml
 *
 * @since 2.0.0
 */
class Bml extends Model\Express
{
    /**
     * Payment method code
     * @var string
     * @since 2.0.0
     */
    protected $_code  = Model\Config::METHOD_WPP_PE_BML;

    /**
     * Checkout payment form
     * @var string
     * @since 2.0.0
     */
    protected $_formBlockType = \Magento\Paypal\Block\Payflow\Bml\Form::class;

    /**
     * Checkout redirect URL getter for onepage checkout
     *
     * @return string
     * @since 2.0.0
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/payflowbml/start');
    }
}
