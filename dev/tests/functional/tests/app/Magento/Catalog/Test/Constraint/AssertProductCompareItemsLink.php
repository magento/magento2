<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductCompareItemsLink
 */
class AssertProductCompareItemsLink extends AbstractConstraint
{
    /**
     * Assert that link "Compare Products..." on top menu of page
     *
     * @param array $products
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(array $products, CmsIndex $cmsIndex)
    {
        $productQty = count($products);
        $qtyOnPage = $cmsIndex->getCompareLinkBlock()->getQtyInCompareList();

        \PHPUnit_Framework_Assert::assertEquals(
            $productQty,
            $qtyOnPage,
            'Qty is not correct in "Compare Products" link.'
        );

        $compareProductUrl = '/catalog/product_compare/';
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($cmsIndex->getLinksBlock()->getLinkUrl('Compare Products'), $compareProductUrl) !== false,
            'Compare product link isn\'t lead to Compare Product Page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return '"Compare Products..." link on top menu of page is correct.';
    }
}
