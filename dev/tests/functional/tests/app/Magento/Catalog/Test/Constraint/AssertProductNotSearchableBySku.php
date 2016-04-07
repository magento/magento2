<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\CatalogSearch\Test\Page\CatalogsearchResult;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductNotSearchableBySku
 * Assert that product cannot be found via Quick Search using searchable product attributes.
 */
class AssertProductNotSearchableBySku extends AbstractConstraint
{
    /**
     * Assert that product cannot be found via Quick Search using searchable product attributes.
     *
     * @param CatalogsearchResult $catalogSearchResult
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        CatalogsearchResult $catalogSearchResult,
        CmsIndex $cmsIndex,
        FixtureInterface $product
    ) {
        $cmsIndex->open();
        $cmsIndex->getSearchBlock()->search($product->getSku());
        \PHPUnit_Framework_Assert::assertFalse(
            $catalogSearchResult->getListProductBlock()->getProductItem($product)->isVisible(),
            'Product was found by SKU.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Product is not searchable by SKU.";
    }
}
