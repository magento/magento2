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

namespace Magento\Customer\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;

/**
 * Class AssertCustomerInGrid
 *
 */
class AssertCustomerInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert customer availability in Customer Grid
     *
     * @param CustomerInjectable $customer
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerInjectable $initialCustomer [optional]
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        CustomerInjectable $customer,
        CustomerIndex $pageCustomerIndex,
        CustomerInjectable $initialCustomer = null
    ) {
        if ($initialCustomer) {
            $customer = $customer->hasData()
                ? array_merge($initialCustomer->getData(), $customer->getData())
                : $initialCustomer->getData();
        } else {
            $customer = $customer->getData();
        }
        $name = (isset($customer['prefix']) ? $customer['prefix'] . ' ' : '')
            . $customer['firstname']
            . (isset($customer['middlename']) ? ' ' . $customer['middlename'] : '')
            . ' ' . $customer['lastname']
            . (isset($customer['suffix']) ? ' ' . $customer['suffix'] : '');
        $filter = [
            'name' => $name,
            'email' => $customer['email'],
        ];

        $pageCustomerIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $pageCustomerIndex->getCustomerGridBlock()->isRowVisible($filter),
            'Customer with '
            . 'name \'' . $filter['name'] . '\', '
            . 'email \'' . $filter['email'] . '\' '
            . 'is absent in Customer grid.'
        );
    }

    /**
     * Text success exist Customer in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer is present in Customer grid.';
    }
}
