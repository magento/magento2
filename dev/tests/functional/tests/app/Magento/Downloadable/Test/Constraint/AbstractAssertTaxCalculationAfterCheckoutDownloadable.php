<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Tax\Test\Constraint\AbstractAssertTaxCalculationAfterCheckout;

/**
 * Checks that prices excl tax on order review and customer order pages are equal to specified in dataset.
 */
abstract class AbstractAssertTaxCalculationAfterCheckoutDownloadable extends AbstractAssertTaxCalculationAfterCheckout
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that prices on order review and customer order pages are equal to specified in dataset.
     *
     * @param array $prices
     * @param InjectableFixture $product
     * @param CheckoutCart $checkoutCart
     * @param CheckoutOnepage $checkoutOnepage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param CustomerOrderView $customerOrderView
     * @return void
     */
    public function processAssert(
        array $prices,
        InjectableFixture $product,
        CheckoutCart $checkoutCart,
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        CustomerOrderView $customerOrderView,
        CmsIndex $cmsIndex
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->customerOrderView = $customerOrderView;

        $checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
        $cmsIndex->getCmsPageBlock()->waitPageInit();
        $checkoutOnepage->getPaymentBlock()->selectPaymentMethod(['method' => 'checkmo']);
        $actualPrices = [];
        $actualPrices = $this->getReviewPrices($actualPrices, $product);
        $actualPrices = $this->getReviewTotals($actualPrices);
        $prices = $this->preparePrices($prices);
        //Order review prices verification
        $message = 'Prices on order review should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, array_filter($actualPrices), $message);

        $checkoutOnepage->getPaymentBlock()->placeOrder();
        $checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId();
        $checkoutOnepageSuccess->getSuccessBlock()->openOrder();
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);

        //Frontend order prices verification
        $message = 'Prices on order view page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, array_filter($actualPrices), $message);
    }
}
