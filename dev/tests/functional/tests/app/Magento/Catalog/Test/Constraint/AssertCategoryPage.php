<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\Category\LandingPage;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Assert that displayed category data on category page equals to passed from fixture.
 */
class AssertCategoryPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    protected $visibleCmsBlockMode = [
        'Static block only',
        'Static block and products'
    ];

    /**
     * Assert that displayed category data on category page equals to passed from fixture.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @param FixtureFactory $fixtureFactory
     * @param CatalogCategoryView $categoryView
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        Category $category,
        Category $initialCategory,
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
        $categoryUrlKey = $category->hasData('url_key')
            ? strtolower($category->getUrlKey())
            : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');
        $categoryUrl = $_ENV['app_frontend_url'] . $categoryUrlKey . '.html';

        $product->persist();
        $browser->open($categoryUrl);

        \PHPUnit_Framework_Assert::assertEquals(
            $categoryUrl,
            $browser->getUrl(),
            'Wrong page URL.'
            . "\nExpected: " . $categoryUrl
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

        if (
            isset($categoryData['landing_page'])
            && isset($categoryData['display_mode'])
            && in_array($categoryData['display_mode'], $this->visibleCmsBlockMode)
        ) {
            /** @var LandingPage $sourceLandingPage */
            $sourceLandingPage = $category->getDataFieldConfig('landing_page')['source'];
            $fixtureContent = $sourceLandingPage->getCmsBlock()->getContent();
            $pageContent = $categoryView->getViewBlock()->getContent();

            \PHPUnit_Framework_Assert::assertEquals(
                $fixtureContent,
                $pageContent,
                'Wrong category landing page content.'
                . "\nExpected: " . $fixtureContent
                . "\nActual: " . $pageContent
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
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category data on category page equals to passed from fixture.';
    }
}
