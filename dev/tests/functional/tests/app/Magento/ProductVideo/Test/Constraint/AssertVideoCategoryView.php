<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert that video is displayed on category page.
 */
class AssertVideoCategoryView extends AbstractConstraint
{
    /**
     * Assert that video is displayed on category page on Store front.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        InjectableFixture $product
    ) {
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
        $src = $catalogCategoryView->getListProductBlock()->getProductItem($product)->getBaseImageSource();
        \PHPUnit\Framework\Assert::assertFalse(
            strpos($src, '/placeholder/') !== false,
            'Video preview image is not displayed on category view when it should.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Video preview images is displayed on category view.';
    }
}
