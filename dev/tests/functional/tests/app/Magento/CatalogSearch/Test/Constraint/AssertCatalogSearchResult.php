<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Mtf\Constraint\AbstractConstraint;

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
        $product = $catalogSearch->getDataFieldConfig('query_text')['source']->getFirstProduct();

        do {
            $isProductVisible = $resultPage->getListProductBlock()->getProductItem($product)->isVisible();
        } while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage());

        \PHPUnit\Framework\Assert::assertTrue(
            $isProductVisible,
            "A product with name '" . $product->getName() . "' was not found."
        );
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
