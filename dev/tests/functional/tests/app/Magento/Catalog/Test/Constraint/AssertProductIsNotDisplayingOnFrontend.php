<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Fixture\FixtureInterface;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

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
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
     * @var Browser
     */
    protected $browser;

    /**
     * Fixture category
     *
     * @var CatalogCategory
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
     * @param Browser $browser
     * @param CatalogCategory|null $category
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CatalogsearchResult $catalogSearchResult,
        CatalogCategoryView $catalogCategoryView,
        CmsIndex $cmsIndex,
        $product,
        Browser $browser,
        CatalogCategory $category = null
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
        if ($this->catalogSearchResult->getListProductBlock()->isProductVisible($product->getName())) {
            $errors[] = '- successful product search.';
        }

        $categoryName = ($product->hasData('category_ids'))
            ? $product->getCategoryIds()[0]
            : $this->category->getName();
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $isProductVisible = $this->catalogCategoryView->getListProductBlock()->isProductVisible($product->getName());
        while (!$isProductVisible && $this->catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $this->catalogCategoryView->getListProductBlock()
                ->isProductVisible($product->getName());
        }

        if ($isProductVisible) {
            $errors[] = '- product with name "{$product->getName()}" is found in this category.';
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
