<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert product price is correct when swatches are selected on category page
 */
class AssertProductPriceWithSelectedSwatchOnCategoryPage extends AbstractConstraint
{
    /**
     * @param CatalogCategoryView $catalogCategoryView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        FixtureInterface $product
    ) {
        $priceBlock = $catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock();
        $configuredPrice = $product->getCheckoutData()['cartItem']['subtotal'];
        \PHPUnit_Framework_Assert::assertEquals(
            number_format($configuredPrice, 2, '.', ''),
            $priceBlock->getPrice(),
            'Product configured price on category page is not correct.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Price with selected swatches is correct.';
    }
}
