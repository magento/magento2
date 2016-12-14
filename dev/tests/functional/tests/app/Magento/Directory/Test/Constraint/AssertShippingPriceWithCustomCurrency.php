<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;

/**
 * Assert that shipping amount is correct in not base currency.
 */
class AssertShippingPriceWithCustomCurrency extends AbstractConstraint
{
    /**
     * Assert that shipping amount is correct in not base currency in the checkout page.
     *
     * @param CmsIndex $cmsIndex
     * @param CheckoutOnepage $checkoutOnepage
     * @param TestStepFactory $testStepFactory
     * @param CatalogProductSimple $product
     * @param CurrencySymbolEntity $currencySymbol
     * @param string $shippingAmount
     * @param array $shipping
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CheckoutOnepage $checkoutOnepage,
        TestStepFactory $testStepFactory,
        CatalogProductSimple $product,
        CurrencySymbolEntity $currencySymbol,
        $shippingAmount,
        array $shipping
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();
        $cmsIndex->getCurrencyBlock()->switchCurrency($currencySymbol);
        $testStepFactory->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => [$product]]
        )->run();
        $testStepFactory->create(\Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class)->run();
        $shipping = [
            'shipping_service' => $shipping['shipping_service'],
            'shipping_method' => $shipping['shipping_method']
        ];
        \PHPUnit_Framework_Assert::assertEquals(
            $shippingAmount,
            $checkoutOnepage->getShippingMethodBlock()->getShippingMethodAmount($shipping),
            'Shipping amount is not correct in the checkout page.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipping amount is correct in the checkout page.';
    }
}
