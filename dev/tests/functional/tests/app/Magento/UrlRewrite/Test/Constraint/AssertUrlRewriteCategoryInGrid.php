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
 * Class AssertUrlRewriteCategoryInGrid
 * Assert that url rewrite category in grid.
 */
class AssertUrlRewriteCategoryInGrid extends AbstractConstraint
{
    const REDIRECT_TYPE_NO = 'No';

    const REDIRECT_TYPE_301 = 'Permanent (301)';

    /**
     * Assert that url rewrite category in grid.
     *
     * @param Category $category
     * @param Category $parentCategory,
     * @param Category $childCategory,
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param string $nestingLevel
     * @param string $filterByPath
     * @return void
     */
    public function processAssert(
        Category $category,
        Category $parentCategory,
        Category $childCategory,
        UrlRewriteIndex $urlRewriteIndex,
        $nestingLevel = null,
        $filterByPath = 'target_path'
    ) {
        $urlRewriteIndex->open();
        if ($nestingLevel) {
            for ($nestingIterator = 0; $nestingIterator < $nestingLevel; $nestingIterator++) {
                $filterByRequestPathCondition[] = $category->getUrlKey();
                $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            }
            $filterByRequestPathConditionString = '';
            $index = count($filterByRequestPathCondition);
            while ($index) {
                $filterByRequestPathConditionString .= $filterByRequestPathCondition[--$index];
                $filterByRequestPathConditionString .= $index ? '/' : '.html';
            }
            $urlPath = strtolower($parentCategory->getUrlKey() . '/' . $childCategory->getUrlKey() . '.html');
            $filter = [
                'request_path' => strtolower($filterByRequestPathConditionString),
                'target_path' => $urlPath,
                'redirect_type' => self::REDIRECT_TYPE_301
            ];
            \PHPUnit_Framework_Assert::assertTrue(
                $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
                'URL Rewrite with request path "' . $filterByRequestPathConditionString . '" is absent in grid.'
            );
        } else {
            $filter = [$filterByPath => strtolower($category->getUrlKey())];
            \PHPUnit_Framework_Assert::assertTrue(
                $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
                'URL Rewrite with request path "' . $category->getUrlKey() . '" is absent in grid.'
            );
        }

        if ($parentCategory && $childCategory) {
            $urlPath = strtolower($parentCategory->getUrlKey() . '/' . $childCategory->getUrlKey() . '.html');
            $filter = [
                'request_path' => $urlPath,
                'target_path' => 'catalog/category/view/id/' . $childCategory->getId(),
                'redirect_type' => self::REDIRECT_TYPE_NO
            ];
            \PHPUnit_Framework_Assert::assertTrue(
                $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
                'URL Rewrite with several conditions is absent in grid.'
            );
        }
    }

    /**
     * URL rewrite category present in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is present in grid.';
    }
}
