<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductInCategory
 */
class AssertProductInCategory extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Checking the product in the page of its price
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @param CatalogCategory $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product,
        CatalogCategory $category
    ) {
        // Open category view page and check visible product
        $categoryName = $category->getName();
        if ($product->hasData('category_ids')) {
            $categoryIds = $product->getCategoryIds();
            $categoryName = is_array($categoryIds) ? reset($categoryIds) : $categoryName;
        }
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isProductVisible,
            'Product is absent on category page.'
        );

        //Process price asserts
        $this->assertPrice($product, $catalogCategoryView);
    }

    /**
     * Verify product price on category view page
     *
     * @param FixtureInterface $product
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    protected function assertPrice(FixtureInterface $product, CatalogCategoryView $catalogCategoryView)
    {
        $price = $catalogCategoryView->getListProductBlock()->getProductPriceBlock($product->getName())
            ->getRegularPrice();

        \PHPUnit_Framework_Assert::assertEquals(
            number_format($product->getPrice(), 2),
            $price,
            'Product regular price on category page is not correct.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price on category page correct.';
    }
}
