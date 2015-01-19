<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Mtf\Constraint\AbstractAssertForm;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertBundleProductDetailsInWishlist
 * Assert that the correct option details are displayed on the "View Details" tool tip
 */
class AssertProductDetailsInWishlist extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that the correct option details are displayed on the "View Details" tool tip
     *
     * @param CmsIndex $cmsIndex
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture $product
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        WishlistIndex $wishlistIndex,
        InjectableFixture $product,
        FixtureFactory $fixtureFactory
    ) {
        $cmsIndex->getLinksBlock()->openLink('My Wish List');
        $actualOptions = $wishlistIndex->getItemsBlock()->getItemProduct($product)->getOptions();
        $cartFixture = $fixtureFactory->createByCode('cart', ['data' => ['items' => ['products' => [$product]]]]);
        $expectedOptions = $cartFixture->getItems()[0]->getData()['options'];

        $errors = $this->verifyData(
            $this->sortDataByPath($expectedOptions, '::title'),
            $this->sortDataByPath($actualOptions, '::title')
        );
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Expected product options are equal to actual.";
    }
}
