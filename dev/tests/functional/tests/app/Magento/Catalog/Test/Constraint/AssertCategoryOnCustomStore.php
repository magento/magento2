<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Assert that Category is present on Custom Store and absent on Main Store.
 */
class AssertCategoryOnCustomStore extends AbstractAssertForm
{
    /**
     * Message on the product page 404.
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Category view.
     *
     * @var CatalogCategoryView
     */
    private $categoryViewPage;

    /**
     * Cms index.
     *
     * @var CmsIndex
     */
    private $cmsIndexPage;

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert Category is present on Custom Store and absent on Main Store:
     * 1. Category is absent on Main Store.
     * 2. Initial Category is absent on Main Store.
     * 3. Category is present on Custom Store.
     *
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @param Category $category
     * @param Category $initialCategory
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogCategoryView $categoryView,
        Category $category,
        Category $initialCategory,
        CmsIndex $cmsIndex
    ) {
        $this->browser = $browser;
        $this->categoryViewPage = $categoryView;
        $this->cmsIndexPage = $cmsIndex;

        $this->verifyUnavailabilityCategoryOnMainStore($category);
        $this->verifyAvailabilityCategoryOnMainStore($initialCategory);
        $this->verifyCategoryOnCustomStore($category);
    }

    /**
     * Verify if category page is unavailable in Main Store.
     *
     * @param Category $category
     * @return void
     */
    private function verifyUnavailabilityCategoryOnMainStore(Category $category)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $this->categoryViewPage->getTitleBlock()->getTitle(),
            'Category ' . $category->getName() . ' is available on Main Store, but should not.'
        );
    }

    /**
     * Verify if category page is available in Main Store.
     *
     * @param Category $category
     * @return void
     */
    private function verifyAvailabilityCategoryOnMainStore(Category $category)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertEquals(
            $category->getName(),
            $this->categoryViewPage->getTitleBlock()->getTitle(),
            'Category ' . $category->getName() . ' is not available on Main Store, but should.'
        );
    }

    /**
     * Verify Category is present in custom store.
     *
     * @param Category $category
     * @return void
     */
    private function verifyCategoryOnCustomStore(Category $category)
    {
        $this->cmsIndexPage->getStoreSwitcherBlock()->selectStoreView($category->getStoreId()['source']->getName());
        $this->cmsIndexPage->getLinksBlock()->waitWelcomeMessage();

        $this->browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');

            \PHPUnit_Framework_Assert::assertEquals(
                $category->getName(),
                $this->categoryViewPage->getTitleBlock()->getTitle(),
                'Category ' . $category->getName() . ' is not available on custom store.'
            );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category displayed in appropriate store.';
    }
}
