<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Sales\Test\Page\OrderView;
use Mtf\Fixture\InjectableFixture;

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
     * @var OrderView
     */
    protected $orderView;

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
     * @param OrderView $orderView
     * @return void
     */
    public function processAssert(
        array $prices,
        InjectableFixture $product,
        CheckoutCart $checkoutCart,
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        OrderView $orderView
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->orderView = $orderView;

        $checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
        $checkoutOnepage->getBillingBlock()->clickContinue();
        $shippingMethod = ['shipping_service' => 'Flat Rate', 'shipping_method' => 'Fixed'];
        $checkoutOnepage->getShippingMethodBlock()->selectShippingMethod($shippingMethod);
        $checkoutOnepage->getShippingMethodBlock()->clickContinue();
        $checkoutOnepage->getPaymentMethodsBlock()->selectPaymentMethod(['method' => 'check_money_order']);
        $checkoutOnepage->getPaymentMethodsBlock()->clickContinue();
        $actualPrices = [];
        $actualPrices = $this->getReviewPrices($actualPrices, $product);
        $actualPrices = $this->getReviewTotals($actualPrices);
        $prices = $this->preparePrices($prices);
        //Order review prices verification
        $message = 'Prices on order review should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);

        $checkoutOnepage->getReviewBlock()->placeOrder();
        $checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId();
        $checkoutOnepageSuccess->getSuccessBlock()->openOrder();
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);

        //Frontend order prices verification
        $message = 'Prices on order view page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
    }

    /**
     * Prepare expected prices prices.
     *
     * @param array $prices
     * @return array $prices
     */
    protected function preparePrices($prices)
    {
        if (isset($prices['category_price_excl_tax'])) {
            unset($prices['category_price_excl_tax']);
        }
        if (isset($prices['category_price_incl_tax'])) {
            unset($prices['category_price_incl_tax']);
        }
        if (isset($prices['product_view_price_excl_tax'])) {
            unset($prices['product_view_price_excl_tax']);
        }
        if (isset($prices['product_view_price_incl_tax'])) {
            unset($prices['product_view_price_incl_tax']);
        }
        return $prices;
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
        $viewBlock = $this->orderView->getOrderViewBlock();
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
