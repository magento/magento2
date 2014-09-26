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
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Reports\Test\Page\Adminhtml\AbandonedCarts;

/**
 * Class AssertAbandonedCartCustomerInfoResult
 * Assert customer info in Abandoned Carts report
 */
class AssertAbandonedCartCustomerInfoResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert customer info in Abandoned Carts report (Reports > Abandoned carts):
     * – name and email
     * – products and qty
     * – created and updated date
     *
     * @param AbandonedCarts $abandonedCarts
     * @param array $products
     * @param CustomerInjectable $customer
     * @return void
     */
    public function processAssert(AbandonedCarts $abandonedCarts, $products, CustomerInjectable $customer)
    {
        $abandonedCarts->open();
        $qty = 0;
        foreach ($products as $product) {
            $qty += $product->getCheckoutData()['options']['qty'];
        }
        $filter = [
            'customer_name' => $customer->getFirstname() . " " . $customer->getLastname(),
            'email' => $customer->getEmail(),
            'items_count' => count($products),
            'items_qty' => $qty,
            'created_at' => date('m/j/Y'),
            'updated_at' => date('m/j/Y')
        ];
        $abandonedCarts->getGridBlock()->search($filter);
        $filter['created_at'] = date('M j, Y');
        $filter['updated_at'] = date('M j, Y');
        \PHPUnit_Framework_Assert::assertTrue(
            $abandonedCarts->getGridBlock()->isRowVisible($filter, false, false),
            'Expected customer info is absent in Abandoned Carts report grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer info in Abandoned Carts report grid is correct.';
    }
}
