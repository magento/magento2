<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogSearchResult
 */
class AssertCatalogSearchResult extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that result page contains all products, according to search request, from fixture
     *
     * @param array $products
     * @param AdvancedResult $resultPage
     * @return void
     */
    public function processAssert(array $products, AdvancedResult $resultPage)
    {
        $errors = [];
        foreach ($products as $product) {
            $name = $product->getName();
            $isProductVisible = $resultPage->getListProductBlock()->isProductVisible($name);
            while (!$isProductVisible && $resultPage->getBottomToolbar()->nextPage()) {
                $isProductVisible = $resultPage->getListProductBlock()->isProductVisible($name);
            }

            if ($isProductVisible === false) {
                $errors[] = '- ' . $name;
            }
        }

        \PHPUnit_Framework_Assert::assertTrue(
            empty($errors),
            'Were not found the following products:' . implode("\n", $errors)
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All products have been successfully found.';
    }
}
