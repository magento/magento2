<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Checks correct pagination for list of products on storefront.
 */
class AssertPaginationCorrectOnStoreFront extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Checks pagination of storefront for correct pagination and list of products
     *
     * @param BrowserInterface $browser
     * @param Category $category
     * @param CatalogCategoryView $catalogCategoryView
     * @param int $productsCount
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        Category $category,
        CatalogCategoryView $catalogCategoryView,
        $productsCount
    ) {
        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            true,
            $catalogCategoryView->getBottomToolbar()->isVisible(),
            'Pagination is not visible'
        );
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogCategoryView->getBottomToolbar()->getLimitedValueByIndex(0),
            $catalogCategoryView->getListProductBlock()->getProductsCount(),
            'Count of products on 1 page does not equivalent with declared in pagination (default value)'
        );
        $catalogCategoryView->getBottomToolbar()->nextPage();
        \PHPUnit_Framework_Assert::assertEquals(
            $this->calculateExpectedProductsCountOnPage(
                $catalogCategoryView->getBottomToolbar()->getLimitedValueByIndex(0),
                2,
                $productsCount
            ),
            $catalogCategoryView->getListProductBlock()->getProductsCount(),
            'Count of products on 2 page does not equivalent with declared in pagination (default value)'
        );
        $catalogCategoryView->getBottomToolbar()->firstPage();
        $catalogCategoryView->getBottomToolbar()->setLimiterValueByIndex(1);
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogCategoryView->getBottomToolbar()->getLimitedValueByIndex(1),
            $catalogCategoryView->getListProductBlock()->getProductsCount(),
            'Count of products on 1 page does not equivalent with declared in pagination(custom value)'
        );
        $catalogCategoryView->getBottomToolbar()->nextPage();
        \PHPUnit_Framework_Assert::assertEquals(
            $this->calculateExpectedProductsCountOnPage(
                $catalogCategoryView->getBottomToolbar()->getLimitedValueByIndex(1),
                2,
                $productsCount
            ),
            $catalogCategoryView->getListProductBlock()->getProductsCount(),
            'Count of products on 2 page does not equivalent with declared in pagination(custom value)'
        );
    }

    /**
     * Calculate expected count of products on current page
     *
     * @param int $productsPerPage
     * @param int $numberOfPage
     * @param int $totalProductsCount
     * @return int
     */
    private function calculateExpectedProductsCountOnPage($productsPerPage, $numberOfPage, $totalProductsCount)
    {
        return min($productsPerPage, $totalProductsCount - $productsPerPage * ($numberOfPage - 1));
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Pagination is correct on frontend.';
    }
}
