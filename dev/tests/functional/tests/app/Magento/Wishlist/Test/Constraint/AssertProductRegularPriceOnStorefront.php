<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
<<<<<<< HEAD
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Asserts that correct product regular price is displayed in default wishlist.
=======

/**
 * Assert that product is present in default wishlist.
>>>>>>> upstream/2.2-develop
 */
class AssertProductRegularPriceOnStorefront extends AbstractConstraint
{
    /**
     * @var string
     */
    private $regularPriceLabel = 'Regular Price';

    /**
<<<<<<< HEAD
     * Asserts that correct product regular price is displayed in default wishlist.
=======
     * Assert that product is present in default wishlist.
>>>>>>> upstream/2.2-develop
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
        if ($product instanceof GroupedProduct) {
=======
        if ($product instanceof \Magento\GroupedProduct\Test\Fixture\GroupedProduct) {
>>>>>>> upstream/2.2-develop
            $associatedProducts = $product->getAssociated();

            /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $associatedProduct */
            foreach ($associatedProducts['products'] as $key => $associatedProduct) {
                $qty = $associatedProducts['assigned_products'][$key]['qty'];
                $price = $associatedProduct->getPrice();
                $productRegularPrice += $qty * $price;
            }
<<<<<<< HEAD
        } elseif ($product instanceof BundleProduct) {
=======
        } elseif ($product instanceof \Magento\Bundle\Test\Fixture\BundleProduct) {
>>>>>>> upstream/2.2-develop
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
        \PHPUnit\Framework\Assert::assertEquals(
=======
        \PHPUnit_Framework_Assert::assertEquals(
>>>>>>> upstream/2.2-develop
            $this->regularPriceLabel,
            $productItem->getPriceLabel(),
            'Wrong product regular price is displayed.'
        );

<<<<<<< HEAD
        \PHPUnit\Framework\Assert::assertNotEmpty(
=======
        \PHPUnit_Framework_Assert::assertNotEmpty(
>>>>>>> upstream/2.2-develop
            $wishListProductRegularPrice,
            'Regular Price for "' . $product->getName() . '" is not correct.'
        );

<<<<<<< HEAD
        \PHPUnit\Framework\Assert::assertEquals(
=======
        \PHPUnit_Framework_Assert::assertEquals(
>>>>>>> upstream/2.2-develop
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
    public function toString(): string
=======
    public function toString()
>>>>>>> upstream/2.2-develop
    {
        return 'Product is displayed with correct regular price.';
    }
}
