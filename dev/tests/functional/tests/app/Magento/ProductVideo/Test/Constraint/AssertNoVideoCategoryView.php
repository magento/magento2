<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert that video is absent on category page.
 */
class AssertNoVideoCategoryView extends AbstractConstraint
{

    /**
     * Assert that video is absent on category page on Store front.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param InjectableFixture $initialProduct
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        InjectableFixture $initialProduct
    ) {
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($initialProduct->getCategoryIds()[0]);
        $src = $catalogCategoryView->getListProductBlock()->getProductItem($initialProduct)->getBaseImageSource();
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($src, '/placeholder/') !== false,
            'Product image is displayed on category view when it should not.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'No product images is displayed on category view.';
    }
}
