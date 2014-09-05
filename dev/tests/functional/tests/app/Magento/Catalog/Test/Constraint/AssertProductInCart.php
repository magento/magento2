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

/**
 * Class AssertProductInCart
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
        $productOptions = $product->getCustomOptions();
        if ($productOptions) {
            $customOption = $catalogProductView->getCustomOptionsBlock();
            $options = $customOption->getOptions();
            $key = $productOptions[0]['title'];
            $customOption->selectProductCustomOption($options[$key]['title']);
        }
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
        $cartBlock = $checkoutCart->getCartBlock();
        $productName = $product->getName();
        $productOptions = $product->getCustomOptions();
        $priceComparing = $product->getPrice();

        if ($groupPrice = $product->getGroupPrice()) {
            $groupPrice = reset($groupPrice);
            $priceComparing = $groupPrice['price'];
        }

        if ($specialPrice = $product->getSpecialPrice()) {
            $priceComparing = $specialPrice;
        }

        if (!empty($productOptions)) {
            $productOption = reset($productOptions);
            $optionsData = reset($productOption['options']);
            $optionName = $cartBlock->getCartItemOptionsNameByProductName($productName);
            $optionValue = $cartBlock->getCartItemOptionsValueByProductName($productName);

            \PHPUnit_Framework_Assert::assertTrue(
                trim($optionName) === $productOption['title']
                && trim($optionValue) === $optionsData['title'],
                'In the cart wrong option product.'
            );

            if ($optionsData['price_type'] === 'Percent') {
                $priceComparing = $priceComparing * (1 + $optionsData['price'] / 100);
            } else {
                $priceComparing += $optionsData['price'];
            }
        }

        $price = $checkoutCart->getCartBlock()->getProductPriceByName($productName);
        \PHPUnit_Framework_Assert::assertEquals(
            number_format($priceComparing, 2),
            $price,
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
