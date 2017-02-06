<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Assert that url rewrite category in grid.
 */
class AssertUrlRewriteCategoryInGrid extends AbstractConstraint
{
    /**
     * Value for no redirect type in grid.
     */
    const REDIRECT_TYPE_NO = 'No';

    /**
     * Curl transport on webapi.
     *
     * @var WebapiDecorator
     */
    private $webApi;

    /**
     * Url rewrite index page.
     *
     * @var UrlRewriteIndex
     */
    private $urlRewriteIndex;

    /**
     * Assert that url rewrite category in grid.
     *
     * @param Category $category
     * @param WebapiDecorator $webApi
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param Category $parentCategory
     * @param Category $childCategory
     * @param string|null $nestingLevel
     * @param string $filterByPath
     * @param string $redirectType
     * @return void
     */
    public function processAssert(
        Category $category,
        WebapiDecorator $webApi,
        UrlRewriteIndex $urlRewriteIndex,
        Category $parentCategory = null,
        Category $childCategory = null,
        $nestingLevel = null,
        $filterByPath = 'target_path',
        $redirectType = 'Permanent (301)'
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->webApi = $webApi;

        $urlRewriteIndex->open();
        $categoryId = $this->getCategoryId($category, $childCategory);
        $nestingPath = $this->getNestingPath($category, $nestingLevel);

        $filter = [
            'request_path' => $nestingPath,
            'target_path' => 'catalog/category/view/id/' . $categoryId,
            'redirect_type' => self::REDIRECT_TYPE_NO
        ];
        if ($parentCategory && $childCategory) {
            $filter['request_path'] =
                strtolower($parentCategory->getUrlKey() . '/' . $childCategory->getUrlKey() . '.html');
        }
        $this->rowVisibleAssertion($filter);

        if ($redirectType != self::REDIRECT_TYPE_NO) {
            if ($parentCategory && $childCategory) {
                $urlPath = strtolower($parentCategory->getUrlKey() . '/' . $childCategory->getUrlKey() . '.html');
                $filter = [
                    'request_path' => $nestingPath,
                    'target_path' => $urlPath,
                    'redirect_type' => $redirectType
                ];
            } else {
                $filter = [$filterByPath => strtolower($category->getUrlKey())];
            }
            $this->rowVisibleAssertion($filter);
        }
    }

    /**
     * Get category id.
     *
     * @param Category $category
     * @param Category|null $childCategory
     * @return int
     */
    private function getCategoryId(Category $category, Category $childCategory = null)
    {
        return ($childCategory ? $childCategory->getId() : $category->getId())
            ? $category->getId()
            : $this->retrieveCategory($category)['id'];
    }

    /**
     * Assert that url rewrite category in grid.
     *
     * @param array $filter
     * @return void
     */
    private function rowVisibleAssertion(array $filter)
    {
        $filterRow = implode(', ', $filter);
        \PHPUnit_Framework_Assert::assertTrue(
            $this->urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
            'URL Rewrite with request path "' . $filterRow . '" is absent in grid.'
        );
    }

    /**
     * Return nesting url path.
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
     * Retrieve category.
     *
     * @param Category $category
     * @return array
     */
    private function retrieveCategory(Category $category)
    {
        $childrenIds = explode(',', $this->getResponse($category->getData('parent_id'))['children']);
        while ($id = array_pop($childrenIds)) {
            $retrieveCategory = $this->getResponse($id);
            if ($retrieveCategory['name'] == $category->getData('name')) {
                return $retrieveCategory;
            }
        }
        return ['id' => null];
    }

    /**
     * Return category data by category id.
     *
     * @param int $categoryId
     * @return array
     */
    private function getResponse($categoryId)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/all/V1/categories/' . $categoryId;
        $this->webApi->write($url, [], WebapiDecorator::GET);
        $response = json_decode($this->webApi->read(), true);
        $this->webApi->close();
        return $response;
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
