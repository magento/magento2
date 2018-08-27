<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that the correct option details are displayed on the "See Details" tooltip.
 */
abstract class AbstractAssertWishlistProductDetails extends AbstractAssertForm
{
    /**
     * @inheritdoc
     */
    protected $skippedFields = ['sku'];

    /**
     * Assert product details.
     *
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture $product
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    protected function assertProductDetails(
        WishlistIndex $wishlistIndex,
        InjectableFixture $product,
        FixtureFactory $fixtureFactory
    ) {
        $productBlock = $wishlistIndex->getWishlistBlock()->getProductItemsBlock();
        $actualOptions = $productBlock->getItemProduct($product)->getOptions();
        $cartFixture = $fixtureFactory->createByCode('cart', ['data' => ['items' => ['products' => [$product]]]]);
        $expectedOptions = $cartFixture->getItems()[0]->getData()['options'];

        $errors = $this->verifyData(
            $this->sortDataByPath($expectedOptions, '::title'),
            $this->sortDataByPath($actualOptions, '::title')
        );
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }
}
