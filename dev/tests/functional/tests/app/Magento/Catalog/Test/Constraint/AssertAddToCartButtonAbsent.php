<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAddToCartButtonAbsent
 * Checks the button on the category/product pages
 */
class AssertAddToCartButtonAbsent extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Category Page
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Index Page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Product simple fixture
     *
     * @var CatalogProductSimple
     */
    protected $product;

    /**
     * Product Page on Frontend
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Assert that "Add to cart" button is not display on page
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     *
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView
    ) {
        $this->catalogCategoryView = $catalogCategoryView;
        $this->cmsIndex = $cmsIndex;
        $this->product = $product;
        $this->catalogProductView = $catalogProductView;

        $this->addToCardAbsentOnCategory();
        $this->addToCardAbsentOnProduct();
    }

    /**
     * "Add to cart" button is not displayed on Category page
     *
     * @return void
     */
    protected function addToCardAbsentOnCategory()
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName(
            $this->product->getCategoryIds()[0]
        );
        \PHPUnit_Framework_Assert::assertFalse(
            $this->catalogCategoryView->getListProductBlock()->checkAddToCardButton(),
            "Button 'Add to Card' is present on Category page"
        );
    }

    /**
     * "Add to cart" button is not display on Product page
     *
     * @return void
     */
    protected function addToCardAbsentOnProduct()
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName(
            $this->product->getCategoryIds()[0]
        );
        $this->catalogCategoryView->getListProductBlock()->openProductViewPage($this->product->getName());
        \PHPUnit_Framework_Assert::assertFalse(
            $this->catalogProductView->getViewBlock()->checkAddToCardButton(),
            "Button 'Add to Card' is present on Product page."
        );
    }

    /**
     * Text absent button "Add to Cart" on the category/product pages
     *
     * @return string
     */
    public function toString()
    {
        return "Button 'Add to Card' is absent on Category page and Product Page.";
    }
}
