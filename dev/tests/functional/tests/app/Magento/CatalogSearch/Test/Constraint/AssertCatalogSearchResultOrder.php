<?php
/**
 * *
 *  * Copyright © 2013-2017 Magento. All rights reserved.
 *  * See COPYING.txt for license details.
 *
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert search results.
 */
class AssertCatalogSearchResultOrder extends AbstractConstraint
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
        /** @var \Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery\QueryText $queryText */
        $queryText = $catalogSearch->getDataFieldConfig('query_text')['source'];
        $products = $queryText->getProducts();

        $productsOrder = [];
        foreach ($products as $productFixture) {
            $productsOrder[] = $productFixture->getData('name');
        }

        do {
            $productNamesOnPage = $resultPage->getListProductBlock()->getProductNames();

            foreach ($productNamesOnPage as $productOnPage) {

                $idxInArray = array_search($productOnPage, $productsOrder, true);
                if (false !== $idxInArray) {
                    if (0 !== $idxInArray) {
                        \PHPUnit_Framework_Assert::assertEmpty(
                            $productsOrder,
                            'Products are in incorrect order on the search result page'
                        );
                    }
                    array_shift($productsOrder);
                }
            }
        } while (count($productsOrder) && $resultPage->getBottomToolbar()->nextPage());

        \PHPUnit_Framework_Assert::assertEmpty(
            $productsOrder,
            'Products are in incorrect order on the search result page'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Searched products were successfully found and they're in the right order.";
    }
}
