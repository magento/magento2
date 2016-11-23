<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;

/**
 * Assert that category redirect is present in grid.
 */
class AssertUrlRewriteRedirectInGrid extends AbstractConstraint
{
    /**
     * Url rewrite index page.
     *
     * @var UrlRewriteIndex
     */
    private $urlRewriteIndex;

    /**
     * Assert that category redirect is present in grid.
     *
     * @param Category $category
     * @param Category $categoryBeforeSave
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param string|null $nestingLevel [optional]
     * @param string $redirectType [optional]
     * @return void
     */
    public function processAssert(
        Category $category,
        Category $categoryBeforeSave,
        UrlRewriteIndex $urlRewriteIndex,
        $nestingLevel = null,
        $redirectType = 'Permanent (301)'
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $urlRewriteIndex->open();
        $filter = [
            'request_path' => $this->getNestingPath($categoryBeforeSave, $nestingLevel),
            'target_path' => $this->getNestingPath($category, $nestingLevel),
            'redirect_type' => $redirectType
        ];
        $this->rowVisibleAssertion($filter);
    }

    /**
     * Assert that category redirect is present in grid.
     *
     * @param array $filter
     * @return void
     */
    private function rowVisibleAssertion(array $filter)
    {
        $filterRow = implode(', ', $filter);
        \PHPUnit_Framework_Assert::assertTrue(
            $this->urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
            'Category redirect with request path "' . $filterRow . '" is absent in grid.'
        );
    }

    /**
     * Return category url path by nesting level.
     *
     * @param Category $category
     * @param int $nestingLevel
     * @return string
     */
    private function getNestingPath(Category $category, $nestingLevel)
    {
        if ($nestingLevel === null) {
            return strtolower($category->getUrlKey() . '.html');
        }
        $filterByRequestPathCondition = [];
        for ($nestingIterator = 0; $nestingIterator < $nestingLevel; $nestingIterator++) {
            $filterByRequestPathCondition[] = $category->getUrlKey();
            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
        }

        return strtolower(implode('/', array_reverse($filterByRequestPathCondition)) . '.html');
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category redirect is present in grid.';
    }
}
