<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Checking the product in the page of its price.
 */
class AssertProductInCategory extends AbstractConstraint
{
    /**
     * Checking the product in the page of its price.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @param Category $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product,
        Category $category
    ) {
        // Open category view page and check visible product
        $categoryName = $category->getName();
        if ($product->hasData('category_ids')) {
            $categoryIds = $product->getCategoryIds();
            $categoryName = is_array($categoryIds) ? reset($categoryIds) : $categoryName;
        }
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        $isProductVisible = $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isProductVisible,
            'Product is absent on category page.'
        );

        //Process price asserts
        $this->assertPrice($product, $catalogCategoryView);
    }

    /**
     * Verify product price on category view page.
     *
     * @param FixtureInterface $product
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    protected function assertPrice(FixtureInterface $product, CatalogCategoryView $catalogCategoryView)
    {
        $priceBlock = $catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock();

        \PHPUnit_Framework_Assert::assertEquals(
            number_format($product->getPrice(), 2, '.', ''),
            $priceBlock->isOldPriceVisible() ? $priceBlock->getOldPrice() : $priceBlock->getPrice(),
            'Product regular price on category page is not correct.'
        );

        if ($product->hasData('special_price')) {
            \PHPUnit_Framework_Assert::assertEquals(
                number_format($product->getSpecialPrice(), 2, '.', ''),
                $priceBlock->getSpecialPrice(),
                'Product special price on category page is not correct.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price on category page correct.';
    }
}
