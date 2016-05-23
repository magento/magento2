<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Multishipping\Test\Page\MultishippingCheckoutOverview;

/**
 * Place order with multiple addresses checkout.
 */
class PlaceOrderStep implements TestStepInterface
{
    /**
     * Multishipping overview page.
     *
     * @var MultishippingCheckoutOverview
     */
    protected $multishippingCheckoutOverview;

    /**
     * @param MultishippingCheckoutOverview $multishippingCheckoutOverview
     */
    public function __construct(MultishippingCheckoutOverview $multishippingCheckoutOverview)
    {
        $this->multishippingCheckoutOverview = $multishippingCheckoutOverview;
    }

    /**
     * Place order with multiple addresses checkout.
     *
     * @return void
     */
    public function run()
    {
        $this->multishippingCheckoutOverview->getOverviewBlock()->placeOrder();
    }
}
