<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert swatches visibility in category page.
 */
class AssertSwatchesVisibilityInCategory extends AbstractConstraint
{
    /**
     * Checking swatches in the category page.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @param bool $visible
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product,
        $visible = true
    ) {
        $this->openCategoryPage($cmsIndex, $product);

        $swatchesBlockVisible = $catalogCategoryView->getListSwatchesProductBlock()
            ->getProductItem($product)->isSwatchesBlockVisible();

        if ($visible) {
            \PHPUnit_Framework_Assert::assertTrue(
                $swatchesBlockVisible,
                'Swatches are absent on category page.'
            );
        } else {
            \PHPUnit_Framework_Assert::assertFalse(
                $swatchesBlockVisible,
                'Swatches are still present on category page.'
            );
        }
    }

    /**
     * Open category view page.
     *
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @return void
     */
    private function openCategoryPage(
        CmsIndex $cmsIndex,
        FixtureInterface $product
    ) {
        $categoryIds = $product->getCategoryIds();
        $categoryName = reset($categoryIds);
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Swatches visibility is correct.';
    }
}
