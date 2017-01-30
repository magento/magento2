<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;

/**
 * Get success placed order id.
 */
class GetPlacedOrderIdStep implements TestStepInterface
{
    /**
     * Order success page.
     *
     * @var CheckoutOnepageSuccess
     */
    protected $checkoutOnepageSuccess;

    /**
     * @constructor
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     */
    public function __construct(CheckoutOnepageSuccess $checkoutOnepageSuccess)
    {
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
    }

    /**
     * Get success placed order id.
     *
     * @return array
     */
    public function run()
    {
        return [
            'orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId(),
        ];
    }
}
