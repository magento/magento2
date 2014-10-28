<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Class AssertProductCompareItemsLink
 */
class AssertProductCompareItemsLink extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
        $qtyOnPage = $cmsIndex->getLinksBlock()->getQtyInCompareList();

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
