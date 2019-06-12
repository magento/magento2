<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
        $productRegularPrice = 0;
        if ($product instanceof \Magento\GroupedProduct\Test\Fixture\GroupedProduct) {
            $associatedProducts = $product->getAssociated();

            /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $associatedProduct */
            foreach ($associatedProducts['products'] as $key => $associatedProduct) {
                $qty = $associatedProducts['assigned_products'][$key]['qty'];
                $price = $associatedProduct->getPrice();
                $productRegularPrice += $qty * $price;
            }
        } elseif ($product instanceof \Magento\Bundle\Test\Fixture\BundleProduct) {
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

        \PHPUnit_Framework_Assert::assertEquals(
            $this->regularPriceLabel,
            $productItem->getPriceLabel(),
            'Wrong product regular price is displayed.'
        );

        \PHPUnit_Framework_Assert::assertNotEmpty(
=======
        $isProductVisible = $wishlistIndex->getWishlistBlock()
            ->getProductItemsBlock()
            ->getItemProduct($product)
            ->isVisible();
        while (!$isProductVisible && $wishlistIndex->getTopToolbar()->nextPage()) {
            $isProductVisible = $wishlistIndex->getWishlistBlock()
                ->getProductItemsBlock()
                ->getItemProduct($product)
                ->isVisible();
        }

        if ($product instanceof GroupedProduct) {
            $productRegularPrice = $this->getGroupedProductRegularPrice($product);
        } elseif ($product instanceof BundleProduct) {
            $productRegularPrice = $this->getBundleProductRegularPrice($product);
        } else {
            $productRegularPrice = (float) $product->getPrice();
        }

        $productItem = $wishlistIndex->getWishlistBlock()
            ->getProductItemsBlock()
            ->getItemProduct($product);

        $wishListProductRegularPrice = $product instanceof BundleProduct
            ? (float)$productItem->getPrice()
            : (float)$productItem->getRegularPrice();

        if (!$product instanceof BundleProduct) {
            \PHPUnit\Framework\Assert::assertEquals(
                $this->regularPriceLabel,
                $productItem->getPriceLabel(),
                'Wrong product regular price is displayed.'
            );
        }

        \PHPUnit\Framework\Assert::assertNotEmpty(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $wishListProductRegularPrice,
            'Regular Price for "' . $product->getName() . '" is not correct.'
        );

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $productRegularPrice,
            $wishListProductRegularPrice,
            'Wrong product regular price is displayed.'
        );
    }

    /**
<<<<<<< HEAD
=======
     * Retrieve grouped product regular price
     *
     * @param GroupedProduct $product
     * @return float
     */
    private function getGroupedProductRegularPrice(GroupedProduct $product)
    {
        $productRegularPrice = 0;
        $associatedProducts = $product->getAssociated();
        /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $associatedProduct */
        foreach ($associatedProducts['products'] as $key => $associatedProduct) {
            $qty = $associatedProducts['assigned_products'][$key]['qty'];
            $price = $associatedProduct->getPrice();
            $productRegularPrice += $qty * $price;
        }
        return $productRegularPrice;
    }

    /**
     * Retrieve bundle product regular price
     *
     * @param BundleProduct $product
     * @return float
     */
    private function getBundleProductRegularPrice(BundleProduct $product)
    {
        $productRegularPrice = 0;
        $bundleSelection = (array) $product->getBundleSelections();
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
        return $productRegularPrice;
    }

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * Returns a string representation of the object.
     *
     * @return string
     */
<<<<<<< HEAD
    public function toString()
=======
    public function toString(): string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return 'Product is displayed with correct regular price.';
    }
}
