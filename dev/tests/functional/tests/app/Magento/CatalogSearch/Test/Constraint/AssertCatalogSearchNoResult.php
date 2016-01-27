<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\CatalogsearchResult;

/**
 * Assert search has no results.
 */
class AssertCatalogSearchNoResult extends AbstractConstraint
{
    /**
     * Assert search has no results and product list in absent.
     *
     * @param CatalogsearchResult $catalogsearchResult
     * @return void
     */
    public function processAssert(CatalogsearchResult $catalogsearchResult)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $catalogsearchResult->getListProductBlock()->isVisible(),
            'Search result has been found.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Search result has not been found.';
    }
}
