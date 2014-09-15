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

namespace Magento\Reports\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Reports\Test\Page\Adminhtml\ShopCartProductReport;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertProductInCartResult
 * Assert that product is present in Products in Carts report grid
 */
class AssertProductInCartResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that product is present in Products in Carts report grid by name, price, carts
     *
     * @param ShopCartProductReport $shopCartProductReport
     * @param CatalogProductSimple $product
     * @param string $carts
     * @return void
     */
    public function processAssert(ShopCartProductReport $shopCartProductReport, CatalogProductSimple $product, $carts)
    {
        $shopCartProductReport->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $shopCartProductReport->getGridBlock()->isProductVisible($product, $carts),
            'Product is absent in Products in Carts report grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present in Products in Carts report grid with correct carts number.';
    }
}
