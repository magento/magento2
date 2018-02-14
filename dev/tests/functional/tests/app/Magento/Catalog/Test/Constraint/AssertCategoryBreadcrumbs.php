<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that displayed breadcrumbs on category page equals to passed from fixture.
 */
class AssertCategoryBreadcrumbs extends AbstractConstraint
{
    /**
     * Name of home page.
     */
    const HOME_PAGE = 'Home';

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert that displayed breadcrumbs on category page equals to passed from fixture.
     *
     * @param BrowserInterface $browser
     * @param Category $category
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        Category $category,
        CatalogCategoryView $catalogCategoryView
    ) {
        $this->browser = $browser;
        $this->openCategory($category);

        $breadcrumbs = $this->getBreadcrumbs($category);
        \PHPUnit_Framework_Assert::assertNotEmpty(
            $breadcrumbs,
            'No breadcrumbs on category \''. $category->getName() . '\' page.'
        );
        $pageBreadcrumbs = $catalogCategoryView->getBreadcrumbs()->getText();
        \PHPUnit_Framework_Assert::assertEquals(
            $breadcrumbs,
            $pageBreadcrumbs,
            'Wrong breadcrumbs of category page.'
            . "\nExpected: " . $breadcrumbs
            . "\nActual: " . $pageBreadcrumbs
        );
    }

    /**
     * Open category.
     *
     * @param Category $category
     * @return void
     */
    protected function openCategory(Category $category)
    {
        $categoryUrlKey = [];

        while ($category) {
            $categoryUrlKey[] = $category->hasData('url_key')
                ? strtolower($category->getUrlKey())
                : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');

            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if ($category !== null && 1 == $category->getParentId()) {
                $category = null;
            }
        }
        $categoryUrlKey = $_ENV['app_frontend_url'] . implode('/', array_reverse($categoryUrlKey)) . '.html';

        $this->browser->open($categoryUrlKey);
    }

    /**
     * Prepare and return category breadcrumbs.
     *
     * @param Category $category
     * @return string
     */
    protected function getBreadcrumbs(Category $category)
    {
        $breadcrumbs = [];

        while ($category) {
            $breadcrumbs[] = $category->getName();

            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if ($category !== null && 1 == $category->getParentId()) {
                $category = null;
            }
        }
        $breadcrumbs[] = self::HOME_PAGE;

        return implode(' ', array_reverse($breadcrumbs));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Breadcrumbs on category page equals to passed from fixture.';
    }
}
