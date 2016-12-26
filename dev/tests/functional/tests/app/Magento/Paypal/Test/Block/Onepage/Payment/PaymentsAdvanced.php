<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Onepage\Payment;

/**
 * Payments Advanced credit card block.
 */
class PaymentsAdvanced extends PaypalIframe
{
    /**
     * Block for filling credit card data for Payments Advanced payment method.
     *
     * @var string
     */
    protected $formBlockCc = \Magento\Paypal\Test\Block\Form\PaymentsAdvanced\Cc::class;
}
