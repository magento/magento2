<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
<<<<<<< HEAD

/**
 * Assert that product is present in default wishlist.
=======
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Asserts that correct product regular price is displayed in default wishlist.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class AssertProductRegularPriceOnStorefront extends AbstractConstraint
{
    /**
     * @var string
     */
    private $regularPriceLabel = 'Regular Price';

    /**
<<<<<<< HEAD
     * Assert that product is present in default wishlist.
=======
     * Asserts that correct product regular price is displayed in default wishlist.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
        if ($product instanceof \Magento\GroupedProduct\Test\Fixture\GroupedProduct) {
=======
        if ($product instanceof GroupedProduct) {
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $associatedProducts = $product->getAssociated();

            /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $associatedProduct */
            foreach ($associatedProducts['products'] as $key => $associatedProduct) {
                $qty = $associatedProducts['assigned_products'][$key]['qty'];
                $price = $associatedProduct->getPrice();
                $productRegularPrice += $qty * $price;
            }
<<<<<<< HEAD
        } elseif ($product instanceof \Magento\Bundle\Test\Fixture\BundleProduct) {
=======
        } elseif ($product instanceof BundleProduct) {
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $this->regularPriceLabel,
            $productItem->getPriceLabel(),
            'Wrong product regular price is displayed.'
        );

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertNotEmpty(
=======
        \PHPUnit\Framework\Assert::assertNotEmpty(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $wishListProductRegularPrice,
            'Regular Price for "' . $product->getName() . '" is not correct.'
        );

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
    public function toString()
=======
    public function toString(): string
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        return 'Product is displayed with correct regular price.';
    }
}
