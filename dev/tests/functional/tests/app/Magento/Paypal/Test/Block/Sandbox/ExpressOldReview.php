<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

/**
 * Old review order block on PayPal side and continue.
 */
class ExpressOldReview extends ExpressReview
{
    /**
     * Continue button on old order review page on PayPal side.
     *
     * @var string
     */
    protected $continue = '#continue';

    /**
     * Total search mask.
     *
     * @var string
     */
    protected $total = '.grandTotal';
}
