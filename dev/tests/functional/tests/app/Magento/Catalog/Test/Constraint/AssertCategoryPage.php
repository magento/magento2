<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\Category\LandingPage;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that displayed category data on category page equals to passed from fixture.
 * NOTE: Design settings, Meta Keywords and Meta Description are not verified.
 */
class AssertCategoryPage extends AbstractConstraint
{
    /**
     * CMS Block display mode.
     *
     * @var array
     */
    protected $visibleCmsBlockMode = [
        'Static block only',
        'Static block and products'
    ];

    /**
     * Category view page.
     *
     * @var CatalogCategoryView
     */
    protected $categoryViewPage;

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Category to test.
     *
     * @var Category
     */
    protected $category;

    /**
     * Assert that displayed category data on category page equals to passed from fixture.
     *
     * @param Category $category
     * @param CatalogCategoryView $categoryView
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        Category $category,
        CatalogCategoryView $categoryView,
        BrowserInterface $browser
    ) {
        $this->browser = $browser;
        $this->category = $category;
        $this->categoryViewPage = $categoryView;
        $this->browser->open($this->getCategoryUrl($category));
        $categoryData = $this->prepareFixtureData($category->getData());
        $diff = $this->verifyGeneralInformation($categoryData);
        $diff = array_merge($diff, $this->verifyContent($categoryData));
        $diff = array_merge($diff, $this->verifyDisplaySettings($categoryData));
        $diff = array_merge($diff, $this->verifySearchEngineOptimization($categoryData));
        \PHPUnit_Framework_Assert::assertEmpty(
            $diff,
            "Category settings on Storefront page are different.\n" . implode(' ', $diff)
        );
    }

    /**
     * Prepares fixture data for comparison.
     *
     * @param array $data
     * @return array
     */
    protected function prepareFixtureData(array $data)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }
        return $data;
    }

    /**
     * Get category url to open.
     *
     * @param Category $category
     * @return string
     */
    protected function getCategoryUrl(Category $category)
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

        return $_ENV['app_frontend_url'] . implode('/', array_reverse($categoryUrlKey)) . '.html';
    }

    /**
     * Verify category general information:
     * # Include in menu
     * # Name
     *
     * @param array $categoryData
     * @return array
     */
    protected function verifyGeneralInformation(array $categoryData)
    {
        $errorMessage = [];

        if (isset($categoryData['include_in_menu']) && $categoryData['include_in_menu'] == 'Yes') {
            if (!$this->categoryViewPage->getTopmenu()->isCategoryVisible($categoryData['name'])) {
                $errorMessage[] = 'Category is not visible in the navigation pane.';
            }
        }
        if (isset($categoryData['include_in_menu']) && $categoryData['include_in_menu'] == 'No') {
            if ($this->categoryViewPage->getTopmenu()->isCategoryVisible($categoryData['name'])) {
                $errorMessage[] = 'Category is visible in the navigation pane.';
            }
        }

        if (isset($categoryData['name'])) {
            $title = $this->categoryViewPage->getTitleBlock()->getTitle();
            if ($categoryData['name'] != $title) {
                $errorMessage[] = 'Wrong category name.'
                    . "\nExpected: " . $categoryData['name']
                    . "\nActual: " . $title;
            }
        }

        return $errorMessage;
    }

    /**
     * Verify category Content data:
     * # Description
     * # CMS Block content
     *
     * @param array $categoryData
     * @return array
     */
    protected function verifyContent(array $categoryData)
    {
        $errorMessage = [];

        if (!$this->categoryViewPage->getViewBlock()->isVisible()) {
            $errorMessage[] =
                'Category Content is not visible.'
                 . "Skipped verifying Content settings for category {$categoryData['name']}.";
            return $errorMessage;
        }

        if (isset($categoryData['description'])) {
            $description = $this->categoryViewPage->getViewBlock()->getDescription();
            if ($categoryData['description'] != $description) {
                $errorMessage[] = 'Wrong category description.'
                    . "\nExpected: " . $categoryData['description']
                    . "\nActual: " . $description;
            }
        }

        if (
            isset($categoryData['landing_page'])
            && isset($categoryData['display_mode'])
            && in_array($categoryData['display_mode'], $this->visibleCmsBlockMode)
        ) {
            /** @var LandingPage $sourceLandingPage */
            $sourceLandingPage = $this->category->getDataFieldConfig('landing_page')['source'];
            $fixtureContent = $sourceLandingPage->getCmsBlock()->getContent();
            $pageContent = $this->categoryViewPage->getViewBlock()->getContent();

            if ($fixtureContent != $pageContent) {
                $errorMessage[] = 'Wrong category landing page content.'
                    . "\nExpected: " . $fixtureContent
                    . "\nActual: " . $pageContent;
            }
        }

        return $errorMessage;
    }

    /**
     * Verify category Display Settings data:
     * # default_sort_by
     * # available_sort_by
     *
     * @param array $categoryData
     * @return array
     */
    protected function verifyDisplaySettings(array $categoryData)
    {
        $errorMessage = [];

        //TODO: verify display_mode

        if (isset($categoryData['default_sort_by'])) {
            $expected = $categoryData['default_sort_by'];
            $actual = $this->categoryViewPage->getTopToolbar()->getSelectSortType();
            if ($expected != $actual) {
                $errorMessage[] = 'Wrong sorting type.'
                    . "\nExpected: " . $expected
                    . "\nActual: " . $actual;
            }
        }

        if (isset($categoryData['available_sort_by'])) {
            $availableSortType = array_filter(
                $categoryData['available_sort_by'],
                function (&$value) {
                    return $value !== '-' && ucfirst($value);
                }
            );
            if ($availableSortType) {
                $expected = array_values($availableSortType);
                $actual = $this->categoryViewPage->getTopToolbar()->getSortType();
                if ($expected != $actual) {
                    $errorMessage[] = 'Wrong available sorting type.'
                        . "\nExpected: " . implode(PHP_EOL, $expected)
                        . "\nActual: " . implode(PHP_EOL, $actual);
                }
            }
        }

        // TODO: verify Layered Navigation Price Step

        return $errorMessage;
    }

    /**
     * Verify category Search Engine Optimization data:
     * # URL
     * # Meta Title
     *
     * @param array $categoryData
     * @return array
     */
    protected function verifySearchEngineOptimization(array $categoryData)
    {
        $errorMessage = [];

        $categoryUrl = $this->getCategoryUrl($this->category);
        if ($categoryUrl != $this->browser->getUrl()) {
            $errorMessage[] = 'Wrong page URL.'
                . "\nExpected: " . $categoryUrl
                . "\nActual: " . $this->browser->getUrl();
        };

        if (isset($categoryData['meta_title'])) {
            $actual = $this->browser->getTitle();
            if ($categoryData['meta_title'] != $actual) {
                $errorMessage[] = 'Wrong page title.'
                    . "\nExpected: " . $categoryData['meta_title']
                    . "\nActual: " . $actual;
            };
        }

        return $errorMessage;
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
