<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Asserts that correct product regular price is displayed in default wishlist.
 */
class AssertProductRegularPriceOnStorefront extends AbstractConstraint
{
    /**
     * @var string
     */
    private $regularPriceLabel = 'Regular Price';

    /**
     * Asserts that correct product regular price is displayed in default wishlist.
     *
     * @param CmsIndex             $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     * @param WishlistIndex        $wishlistIndex
     * @param InjectableFixture    $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex,
        WishlistIndex $wishlistIndex,
        InjectableFixture $product
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->openLink('My Account');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Wish List');

        $productRegularPrice = 0;
        if ($product instanceof GroupedProduct) {
            $associatedProducts = $product->getAssociated();

            /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $associatedProduct */
            foreach ($associatedProducts['products'] as $key => $associatedProduct) {
                $qty = $associatedProducts['assigned_products'][$key]['qty'];
                $price = $associatedProduct->getPrice();
                $productRegularPrice += $qty * $price;
            }
        } elseif ($product instanceof BundleProduct) {
            $bundleSelection = (array)$product->getBundleSelections();
            foreach ($bundleSelection['products'] as $bundleOption) {
                $regularBundleProductPrice = 0;
                /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $bundleProduct */
                foreach ($bundleOption as $bundleProduct) {
                    $checkoutData = $bundleProduct->getCheckoutData();
                    $bundleProductPrice = $checkoutData['qty'] * $checkoutData['cartItem']['price'];
                    if (0 === $regularBundleProductPrice) {
                        $regularBundleProductPrice = $bundleProductPrice;
                    } else {
                        $regularBundleProductPrice = max([$bundleProductPrice, $regularBundleProductPrice]);
                    }
                }
                $productRegularPrice += $regularBundleProductPrice;
            }
        } else {
            $productRegularPrice = (float)$product->getPrice();
        }

        $productItem = $wishlistIndex->getWishlistBlock()->getProductItemsBlock()->getItemProduct($product);
        $wishListProductRegularPrice = (float)$productItem->getRegularPrice();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->regularPriceLabel,
            $productItem->getPriceLabel(),
            'Wrong product regular price is displayed.'
        );

        \PHPUnit\Framework\Assert::assertNotEmpty(
            $wishListProductRegularPrice,
            'Regular Price for "' . $product->getName() . '" is not correct.'
        );

        \PHPUnit\Framework\Assert::assertEquals(
            $productRegularPrice,
            $wishListProductRegularPrice,
            'Wrong product regular price is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'Product is displayed with correct regular price.';
    }
}
