<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertBundlePriceType
 */
class AssertBundlePriceType extends AbstractConstraint
{
    /**
     * Product price type
     *
     * @var string
     */
    protected $productPriceType = 'Yes';

    /**
     * Assert that displayed price for bundle items on shopping cart page equals to passed from fixture.
     *   Price for bundle items has two options:
     *   1. Fixed (price of bundle product)
     *   2. Dynamic (price of bundle item)
     *
     * @param CatalogProductView $catalogProductView
     * @param BundleProduct $product
     * @param CheckoutCart $checkoutCartView
     * @param BrowserInterface $browser
     * @param BundleProduct $originalProduct [optional]
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BundleProduct $product,
        CheckoutCart $checkoutCartView,
        BrowserInterface $browser,
        BundleProduct $originalProduct = null
    ) {
        $checkoutCartView->open()->getCartBlock()->clearShoppingCart();
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        //Process assertions
        $this->assertPrice($product, $catalogProductView, $checkoutCartView, $originalProduct);
    }

    /**
     * Assert prices on the product view page and shopping cart page.
     *
     * @param BundleProduct $product
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCartView
     * @param BundleProduct $originalProduct [optional]
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function assertPrice(
        BundleProduct $product,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCartView,
        BundleProduct $originalProduct = null
    ) {
        $bundleData = $product->getData();
        $this->productPriceType = $originalProduct !== null
            ? $originalProduct->getPriceType()
            : $product->getPriceType();
        $catalogProductView->getViewBlock()->addToCart($product);
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $checkoutCartView->open();
        $cartItem = $checkoutCartView->getCartBlock()->getCartItem($product);
        $specialPrice = 0;

        $optionPrice = [];
        $fillData = $product->getCheckoutData();
        foreach ($fillData['options']['bundle_options'] as $key => $data) {
            $subProductPrice = 0;
            foreach ($bundleData['bundle_selections']['products'][$key] as $productKey => $itemProduct) {
                if (strpos($itemProduct->getName(), $data['value']['name']) !== false) {
                    $data['value']['key'] = $productKey;
                    $subProductPrice = $itemProduct->getPrice();
                }
            }

            $optionPrice[$key]['price'] = $this->productPriceType == 'No'
                ? number_format(
                    $bundleData['bundle_selections']['bundle_options'][$key]['assigned_products'][$data['value']['key']]
                    ['data']['selection_price_value'],
                    2
                )
                : number_format($subProductPrice, 2);
        }

        foreach ($optionPrice as $index => $item) {
            $item['price'] -= $item['price'] * $specialPrice;
            \PHPUnit\Framework\Assert::assertEquals(
                number_format($item['price'], 2),
                $cartItem->getPriceBundleOptions($index + 1),
                'Bundle item ' . ($index + 1) . ' options on frontend don\'t equal to fixture.'
            );
        }
        $sumOptionsPrice = $product->getDataFieldConfig('price')['source']->getPriceData()['cart_price'];

        $subTotal = number_format($cartItem->getPrice(), 2);
        \PHPUnit\Framework\Assert::assertEquals(
            $sumOptionsPrice,
            $subTotal,
            'Bundle unit price on frontend doesn\'t equal to fixture.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle price on shopping cart page is not correct.';
    }
}
