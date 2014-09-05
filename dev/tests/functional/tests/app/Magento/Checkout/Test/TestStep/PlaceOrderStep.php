<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertOrderTotalOnReviewPage;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Mtf\TestStep\TestStepInterface;

/**
 * Class PlaceOrderStep
 * Place order in one page checkout
 */
class PlaceOrderStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Assert that Order Grand Total is correct on checkout page review block
     *
     * @var AssertOrderTotalOnReviewPage
     */
    protected $assertOrderTotalOnReviewPage;

    /**
     * One page checkout success page
     *
     * @var CheckoutOnepageSuccess
     */
    protected $checkoutOnepageSuccess;

    /**
     * Grand total price
     *
     * @var string
     */
    protected $grandTotal;

    /**
     * Checkout method
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * @construct
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertOrderTotalOnReviewPage $assertOrderTotalOnReviewPage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param string $checkoutMethod
     * @param string $grandTotal
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertOrderTotalOnReviewPage $assertOrderTotalOnReviewPage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        $checkoutMethod,
        $grandTotal
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertOrderTotalOnReviewPage = $assertOrderTotalOnReviewPage;
        $this->grandTotal = $grandTotal;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Place order after checking order totals on review step
     *
     * @return array
     */
    public function run()
    {
        $this->assertOrderTotalOnReviewPage->processAssert($this->checkoutOnepage, $this->grandTotal);
        $this->checkoutOnepage->getReviewBlock()->placeOrder();

        return ['orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId()];
    }
}
