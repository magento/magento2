<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $urlRewriteIndex->open();
        if ($redirectType == 'No') {
            if ($parentCategory && $childCategory) {
                $categoryId = $childCategory->getId();
            } else {
                $categoryId = $category->getId() ?
                    $category->getId() :
                    $this->retrieveCategoryById($webApi, $category->getData('parent_id'))['children'];
            }
            $urlPath = $nestingLevel ?
                $this->getNestingPath($category, $nestingLevel) :
                strtolower($category->getUrlKey() . '.html');

            $filter = [
                'request_path' => $urlPath,
                'target_path' => 'catalog/category/view/id/' . $categoryId,
                'redirect_type' => self::REDIRECT_TYPE_NO
            ];
            return $this->rowVisibleAssertion($filter);
        }
        if ($parentCategory && $childCategory) {
            $urlPath = strtolower($parentCategory->getUrlKey() . '/' . $childCategory->getUrlKey() . '.html');

            $filter = [
                'request_path' => $this->getNestingPath($category, $nestingLevel),
                'target_path' => $urlPath,
                'redirect_type' => $redirectType
            ];
        } else {
            $filter = [$filterByPath => strtolower($category->getUrlKey())];
        }
        $this->rowVisibleAssertion($filter);
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
     * @param $nestingLevel
     * @return string
     */
    private function getNestingPath(Category $category, $nestingLevel)
    {
        $filterByRequestPathCondition = [];
        for ($nestingIterator = 0; $nestingIterator < $nestingLevel; $nestingIterator++) {
            $filterByRequestPathCondition[] = $category->getUrlKey();
            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
        }

        return strtolower(implode('/', array_reverse($filterByRequestPathCondition)) . '.html');
    }

    /**
     * Retrieve category by parent id.
     *
     * @param WebapiDecorator $webApi
     * @param $id
     * @return mixed
     */
    public function retrieveCategoryById(WebapiDecorator $webApi, $id)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/all/V1/categories/' . $id;
        $webApi->write($url, [], WebapiDecorator::GET);
        $response = json_decode($webApi->read(), true);
        $webApi->close();
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
