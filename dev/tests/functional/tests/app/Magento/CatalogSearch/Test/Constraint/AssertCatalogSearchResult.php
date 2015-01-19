<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Mtf\Constraint\AbstractConstraint;

/**
 * Assert search results.
 */
class AssertCatalogSearchResult extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that result page contains product, according to search request from fixture.
     *
     * @param CatalogSearchQuery $catalogSearch
     * @param AdvancedResult $resultPage
     * @return void
     */
    public function processAssert(CatalogSearchQuery $catalogSearch, AdvancedResult $resultPage)
    {
        $product = $catalogSearch->getDataFieldConfig('query_text')['source']->getProduct();
        $name = $product->getName();
        $isProductVisible = $resultPage->getListProductBlock()->isProductVisible($name);
        while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage()) {
            $isProductVisible = $resultPage->getListProductBlock()->isProductVisible($name);
        }

        \PHPUnit_Framework_Assert::assertTrue($isProductVisible, "A product with name '$name' was not found.");
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Searched product has been successfully found.';
    }
}
