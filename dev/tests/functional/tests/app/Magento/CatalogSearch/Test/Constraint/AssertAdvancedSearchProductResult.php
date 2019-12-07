<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Advanced Search result page contains only product(s) according to requested from fixture.
 */
class AssertAdvancedSearchProductResult extends AbstractConstraint
{
    /**
     * Text for founded product.
     */
    const FOUNDED_PRODUCT_MESSAGE = 'Product %s is founded';

    /**
     * Assert that Advanced Search result page contains only product(s) according to requested from fixture.
     *
     * @param array $isVisibleInAdvancedSearch
     * @param array $allProducts
     * @param AdvancedResult $resultPage
     * @return void
     */
    public function processAssert(
        array $isVisibleInAdvancedSearch,
        array $allProducts,
        AdvancedResult $resultPage
    ) {
        $expectedResult = $this->prepareExpectedResult($isVisibleInAdvancedSearch, $allProducts);
        $foundedProducts = $this->advancedSearchProducts($resultPage, $allProducts);
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedResult,
            $foundedProducts,
            'Expected and founded products not the same.'
            . "\nExpected: " . print_r($expectedResult)
            . "\nActual: " . print_r($foundedProducts)
        );
    }

    /**
     * Returns array with expected products.
     *
     * @param array $isVisibleInAdvancedSearch
     * @param array $products
     * @return array
     */
    private function prepareExpectedResult(array $isVisibleInAdvancedSearch, array $products)
    {
        $expectedResult = [];
        foreach ($isVisibleInAdvancedSearch as $key => $value) {
            if ($value == "Yes") {
                $expectedResult[] = sprintf(self::FOUNDED_PRODUCT_MESSAGE, $products[$key]->getName());
            }
        }
        sort($expectedResult);
        return $expectedResult;
    }

    /**
     * Returns array with found products.
     *
     * @param AdvancedResult $resultPage
     * @param array $allProducts
     * @return array
     */
    private function advancedSearchProducts(AdvancedResult $resultPage, array $allProducts)
    {
        $products = $allProducts;
        $foundedProducts = [];
        do {
            $dirtKeys = [];
            foreach ($allProducts as $key => $product) {
                $isProductVisible = $resultPage->getListProductBlock()->getProductItem($product)->isVisible();
                if ($isProductVisible) {
                    $foundedProducts[] = sprintf(self::FOUNDED_PRODUCT_MESSAGE, $products[$key]->getName());
                    $dirtKeys[] = $key;
                }
            }
            foreach ($dirtKeys as $key) {
                unset($products[$key]);
            }
        } while ($resultPage->getBottomToolbar()->nextPage() && (count($products) > 0));

        sort($foundedProducts);
        return $foundedProducts;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All products are involved in the search were found successfully.';
    }
}
