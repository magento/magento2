<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\Payflow;

use Magento\Paypal\Model;

class Bml extends Model\Express
{
    /**
     * Payment method code
     * @var string
     */
    protected $_code  = Model\Config::METHOD_WPP_PE_BML;

    /**
     * Checkout payment form
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Payflow\Bml\Form';

    /**
     * Checkout redirect URL getter for onepage checkout
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/payflowbml/start');
    }
}
