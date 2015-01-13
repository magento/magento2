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
 * Class AssertProductVisibleInCategory
 */
class AssertProductVisibleInCategory extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Displays an error message
     *
     * @var string
     */
    protected $errorMessage = 'Product is absent on category page.';

    /**
     * Message for passing test
     *
     * @var string
     */
    protected $successfulMessage = 'Product is visible in the assigned category.';

    /**
     * Assert that product is visible in the assigned category
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @param CatalogCategory|null $category
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        FixtureInterface $product,
        CatalogCategory $category = null
    ) {
        $categoryName = $product->hasData('category_ids') ? $product->getCategoryIds()[0] : $category->getName();
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        }

        if (($product->getVisibility() === 'Search') || ($this->getStockStatus($product) === 'Out of Stock')) {
            $isProductVisible = !$isProductVisible;
            $this->errorMessage = 'Product found in this category.';
            $this->successfulMessage = 'Asserts that the product could not be found in this category.';
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isProductVisible,
            $this->errorMessage
        );
    }

    /**
     * Getting is in stock status
     *
     * @param FixtureInterface $product
     * @return string|null
     */
    protected function getStockStatus(FixtureInterface $product)
    {
        $quantityAndStockStatus = $product->getQuantityAndStockStatus();
        return isset($quantityAndStockStatus['is_in_stock']) ? $quantityAndStockStatus['is_in_stock'] : null;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return $this->successfulMessage;
    }
}
