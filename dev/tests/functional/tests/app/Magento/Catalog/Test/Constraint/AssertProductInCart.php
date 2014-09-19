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

namespace Magento\Catalog\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Fixture\FixtureInterface;
use Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertProductInCart
 * Assertion that the product is correctly displayed in cart
 */
class AssertProductInCart extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assertion that the product is correctly displayed in cart
     *
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @param Browser $browser
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        FixtureInterface $product,
        Browser $browser,
        CheckoutCart $checkoutCart
    ) {
        // Add product to cart
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->fillOptions($product);
        $catalogProductView->getViewBlock()->clickAddToCart();

        // Check price
        $this->assertOnShoppingCart($product, $checkoutCart);
    }

    /**
     * Assert prices on the shopping cart
     *
     * @param FixtureInterface $product
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    protected function assertOnShoppingCart(FixtureInterface $product, CheckoutCart $checkoutCart)
    {
        /** @var CatalogProductSimple $product */
        $customOptions = $product->getCustomOptions();
        $checkoutData = $product->getCheckoutData();
        $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
            ? $checkoutData['options']['custom_options']
            : [];
        $fixturePrice = $product->getPrice();
        $groupPrice = $product->getGroupPrice();
        $specialPrice = $product->getSpecialPrice();
        $cartItem = $checkoutCart->getCartBlock()->getCartItem($product);
        $formPrice = $cartItem->getPrice();

        if ($groupPrice) {
            $groupPrice = reset($groupPrice);
            $fixturePrice = $groupPrice['price'];
        }
        if ($specialPrice) {
            $fixturePrice = $specialPrice;
        }
        $fixtureActualPrice = $fixturePrice;

        foreach ($checkoutCustomOptions as $checkoutOption) {
            $attributeKey = str_replace('attribute_key_', '', $checkoutOption['title']);
            $optionKey = str_replace('option_key_', '', $checkoutOption['value']);
            $option = $customOptions[$attributeKey]['options'][$optionKey];

            if ('Fixed' == $option['price_type']) {
                $fixtureActualPrice += $option['price'];
            } else {
                $fixtureActualPrice += ($fixturePrice / 100) * $option['price'];
            }
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $fixtureActualPrice,
            $formPrice,
            'Product price in shopping cart is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is correctly displayed in cart.';
    }
}
