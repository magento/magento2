<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertCategoryPage
 * Assert that displayed category data on category page equals to passed from fixture
 */
class AssertCategoryPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that displayed category data on category page equals to passed from fixture
     *
     * @param CatalogCategory $category
     * @param CatalogCategory $initialCategory
     * @param FixtureFactory $fixtureFactory
     * @param CatalogCategoryView $categoryView
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogCategory $category,
        CatalogCategory $initialCategory,
        FixtureFactory $fixtureFactory,
        CatalogCategoryView $categoryView,
        Browser $browser
    ) {
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'default',
                'data' => [
                    'category_ids' => [
                        'category' => $initialCategory,
                    ],
                ]
            ]
        );
        $categoryData = array_merge($initialCategory->getData(), $category->getData());
        $product->persist();
        $url = $_ENV['app_frontend_url'] . strtolower($category->getUrlKey()) . '.html';
        $browser->open($url);
        \PHPUnit_Framework_Assert::assertEquals(
            $url,
            $browser->getUrl(),
            'Wrong page URL.'
            . "\nExpected: " . $url
            . "\nActual: " . $browser->getUrl()
        );

        if (isset($categoryData['name'])) {
            $title = $categoryView->getTitleBlock()->getTitle();
            \PHPUnit_Framework_Assert::assertEquals(
                $categoryData['name'],
                $title,
                'Wrong page title.'
                . "\nExpected: " . $categoryData['name']
                . "\nActual: " . $title
            );
        }

        if (isset($categoryData['description'])) {
            $description = $categoryView->getViewBlock()->getDescription();
            \PHPUnit_Framework_Assert::assertEquals(
                $categoryData['description'],
                $description,
                'Wrong category description.'
                . "\nExpected: " . $categoryData['description']
                . "\nActual: " . $description
            );
        }

        if (isset($categoryData['default_sort_by'])) {
            $sortBy = strtolower($categoryData['default_sort_by']);
            $sortType = $categoryView->getTopToolbar()->getSelectSortType();
            \PHPUnit_Framework_Assert::assertEquals(
                $sortBy,
                $sortType,
                'Wrong sorting type.'
                . "\nExpected: " . $sortBy
                . "\nActual: " . $sortType
            );
        }

        if (isset($categoryData['available_sort_by'])) {
            $availableSortType = array_filter(
                $categoryData['available_sort_by'],
                function (&$value) {
                    return $value !== '-' && ucfirst($value);
                }
            );
            if ($availableSortType) {
                $availableSortType = array_values($availableSortType);
                $availableSortTypeOnPage = $categoryView->getTopToolbar()->getSortType();
                \PHPUnit_Framework_Assert::assertEquals(
                    $availableSortType,
                    $availableSortTypeOnPage,
                    'Wrong available sorting type.'
                    . "\nExpected: " . implode(PHP_EOL, $availableSortType)
                    . "\nActual: " . implode(PHP_EOL, $availableSortTypeOnPage)
                );
            }
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Category data on category page equals to passed from fixture.';
    }
}
