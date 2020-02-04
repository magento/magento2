<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Checks that prices excluding tax on order review and customer order pages are equal to specified in dataset.
 */
abstract class AbstractAssertTaxCalculationAfterCheckout extends AbstractConstraint
{
    /**
     * Checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Order view page.
     *
     * @var CustomerOrderView
     */
    protected $customerOrderView;

    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Implementation for get order review total prices function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getReviewTotals($actualPrices);

    /**
     * Implementation for get order total prices from customer account function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getOrderTotals($actualPrices);

    /**
     * Assert that prices on order review and customer order pages are equal to specified in dataset.
     *
     * @param array $prices
     * @param InjectableFixture $product
     * @param CheckoutCart $checkoutCart
     * @param CheckoutOnepage $checkoutOnepage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param CustomerOrderView $customerOrderView
     * @param CmsIndex $cmsIndex
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

        $shippingMethod = ['shipping_service' => 'Flat Rate', 'shipping_method' => 'Fixed'];
        $checkoutOnepage->getShippingMethodBlock()->selectShippingMethod($shippingMethod);
        $checkoutOnepage->getShippingMethodBlock()->clickContinue();
        $checkoutOnepage->getPaymentBlock()->selectPaymentMethod(['method' => 'checkmo']);
        $actualPrices = [];
        $actualPrices = $this->getReviewPrices($actualPrices, $product);
        $actualPrices = $this->getReviewTotals($actualPrices);
        $prices = $this->preparePrices($prices);
        //Order review prices verification
        $message = 'Prices on order review should be equal to defined in dataset.';
        \PHPUnit\Framework\Assert::assertEquals(
            array_diff_key($prices, ['cart_item_price_excl_tax' => null, 'cart_item_price_incl_tax' => null]),
            array_diff_key($actualPrices, ['cart_item_price_excl_tax' => null, 'cart_item_price_incl_tax' => null]),
            $message
        );

        $checkoutOnepage->getPaymentBlock()->placeOrder();
        $checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId();
        $checkoutOnepageSuccess->getSuccessBlock()->openOrder();
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);

        //Frontend order prices verification
        $message = 'Prices on order view page should be equal to defined in dataset.';
        \PHPUnit\Framework\Assert::assertEquals($prices, $actualPrices, $message);
    }

    /**
     * Prepare expected prices prices.
     *
     * @param array $prices
     * @return array
     */
    protected function preparePrices($prices)
    {
        return array_diff_key(
            $prices,
            array_flip([
                'category_price',
                'category_special_price',
                'category_price_excl_tax',
                'category_price_incl_tax',
                'product_view_price',
                'product_view_special_price',
                'product_view_price_excl_tax',
                'product_view_price_incl_tax'
            ])
        );
    }

    /**
     * Get review product prices.
     *
     * @param InjectableFixture $product
     * @param $actualPrices
     * @return array
     */
    public function getReviewPrices($actualPrices, InjectableFixture $product)
    {
        $reviewBlock = $this->checkoutOnepage->getReviewBlock();
        $actualPrices['cart_item_price_excl_tax'] = $reviewBlock->getItemPriceExclTax($product->getName());
        $actualPrices['cart_item_price_incl_tax'] = $reviewBlock->getItemPriceInclTax($product->getName());
        $actualPrices['cart_item_subtotal_excl_tax'] = $reviewBlock->getItemSubExclTax($product->getName());
        $actualPrices['cart_item_subtotal_incl_tax'] = $reviewBlock->getItemSubInclTax($product->getName());
        return $actualPrices;
    }

    /**
     * Get order product prices.
     *
     * @param InjectableFixture $product
     * @param $actualPrices
     * @return array
     */
    public function getOrderPrices($actualPrices, InjectableFixture $product)
    {
        $viewBlock = $this->customerOrderView->getOrderViewBlock();
        $actualPrices['cart_item_price_excl_tax'] = $viewBlock->getItemPriceExclTax($product->getName());
        $actualPrices['cart_item_price_incl_tax'] = $viewBlock->getItemPriceInclTax($product->getName());
        $actualPrices['cart_item_subtotal_excl_tax'] = $viewBlock->getItemSubExclTax($product->getName());
        $actualPrices['cart_item_subtotal_incl_tax'] = $viewBlock->getItemSubInclTax($product->getName());
        return $actualPrices;
    }

    /**
     * Text of price verification after order creation
     *
     * @return string
     */
    public function toString()
    {
        return 'Prices on front after order creation is correct.';
    }
}
