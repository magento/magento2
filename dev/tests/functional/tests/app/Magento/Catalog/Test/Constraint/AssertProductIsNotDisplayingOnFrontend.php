<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductIsNotDisplayingOnFrontend
 */
class AssertProductIsNotDisplayingOnFrontend extends AbstractConstraint
{
    /**
     * Message on the product page 404
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Catalog category view page
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Catalog search result page
     *
     * @var CatalogsearchResult
     */
    protected $catalogSearchResult;

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Browser
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Fixture category
     *
     * @var Category
     */
    protected $category;

    /**
     * Assert that product with current configurations is not displayed on front-end
     *
     * @param CatalogProductView $catalogProductView
     * @param CatalogsearchResult $catalogSearchResult
     * @param CatalogCategoryView $catalogCategoryView
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface|FixtureInterface[] $product
     * @param BrowserInterface $browser
     * @param Category|null $category
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CatalogsearchResult $catalogSearchResult,
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        $product,
        BrowserInterface $browser,
        Category $category = null
    ) {
        $this->browser = $browser;
        $this->catalogProductView = $catalogProductView;
        $this->catalogSearchResult = $catalogSearchResult;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->cmsIndex = $cmsIndex;
        $this->category = $category;
        $products = is_array($product) ? $product : [$product];
        $errors = [];
        foreach ($products as $product) {
            $errors = array_merge($errors, $this->isNotDisplayingOnFrontendAssert($product));
        }
        \PHPUnit_Framework_Assert::assertEmpty(
            $errors,
            "In the process of checking product availability on the frontend, found the following errors:\n"
            . implode("\n", $errors)
        );
    }

    /**
     * Verify product displaying on frontend
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function isNotDisplayingOnFrontendAssert(FixtureInterface $product)
    {
        $errors = [];
        // Check the product page is not available
        // TODO fix initialization url for frontend page
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $titleBlock = $this->catalogProductView->getTitleBlock();

        if ($titleBlock->getTitle() !== self::NOT_FOUND_MESSAGE) {
            $errors[] = '- the headline on the page does not match, the text should be -> "'
                . self::NOT_FOUND_MESSAGE . '".';
        }

        $this->cmsIndex->open();
        $this->cmsIndex->getSearchBlock()->search($product->getSku());
        if ($this->catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible()) {
            $errors[] = '- successful product search.';
        }

        $categoryName = ($product->hasData('category_ids'))
            ? $product->getCategoryIds()[0]
            : $this->category->getName();
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $isProductVisible = $this->catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        while (!$isProductVisible && $this->catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $this->catalogCategoryView->getListProductBlock()
                ->getProductItem($product)->isVisible();
        }

        if ($isProductVisible) {
            $errors[] = "- product with name '{$product->getName()}' is found in this category.";
        }

        return $errors;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Assertion that the product is not available on the pages of the frontend.';
    }
}
