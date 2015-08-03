<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Paypal\Test\Fixture\SandboxCustomer;
use Magento\Paypal\Test\Page\Sandbox\ExpressReview;
use Magento\Paypal\Test\Constraint\Sandbox\AssertTotalPaypalReview;

/**
 * Review order on PayPal side and continue.
 */
class ContinuePaypalCheckoutStep implements TestStepInterface
{
    /**
     * PayPal Sandbox customer fixture.
     *
     * @var SandboxCustomer
     */
    protected $sandboxCustomer;

    /**
     * Order review page on PayPal side.
     *
     * @var ExpressReview
     */
    protected $expressReview;

    /**
     * Order total on PayPal side.
     *
     * @var array
     */
    protected $total;

    /**
     * @constructor
     * @param SandboxCustomer $sandboxCustomer
     * @param ExpressReview $expressReview
     * @param AssertTotalPaypalReview $assertTotalPaypalReview
     * @param array $paypalPrices
     */
    public function __construct(
        SandboxCustomer $sandboxCustomer,
        ExpressReview $expressReview,
        AssertTotalPaypalReview $assertTotalPaypalReview,
        array $paypalPrices = []
    ) {
        $this->sandboxCustomer = $sandboxCustomer;
        $this->expressReview = $expressReview;
        $this->assertTotalPaypalReview = $assertTotalPaypalReview;
        $this->paypalPrices = $paypalPrices;
    }

    /**
     * Review order on PayPal side and continue.
     *
     * @return void
     */
    public function run()
    {
        $this->expressReview->getExpressLoginBlock()->waitForFormLoaded();
        if ($this->expressReview->getExpressMainLoginBlock()->isVisible()) {
            $this->expressReview->getExpressMainLoginBlock()->getLoginBlock()->fill($this->sandboxCustomer);
            $this->expressReview->getExpressMainLoginBlock()->getLoginBlock()->sandboxLogin();
        }
        if (isset($this->paypalPrices['total'])) {
            $this->assertTotalPaypalReview->processAssert($this->expressReview, $this->paypalPrices['total']);
        }
        $this->expressReview->getExpressMainReviewBlock()->getReviewBlock()->reviewAndContinue();
    }
}
