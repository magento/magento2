<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Onepage\Payment;

/**
 * Hosted Pro credit card block.
 */
class HostedPro extends PaypalIframe
{
    /**
     * Block for filling credit card data for Hosted Pro payment method.
     *
     * @var string
     */
    protected $formBlockCc = \Magento\Paypal\Test\Block\Form\HostedPro\Cc::class;
}
