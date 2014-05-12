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
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Class AssertCustomerInGrid
 *
 */
class AssertCustomerForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert that displayed customer data on edit page(backend) equals passed from fixture
     *
     * @param CustomerInjectable $customer
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexEdit $pageCustomerIndexEdit
     * @param AddressInjectable $address [optional]
     * @return void
     */
    public function processAssert(
        CustomerInjectable $customer,
        CustomerIndex $pageCustomerIndex,
        CustomerIndexEdit $pageCustomerIndexEdit,
        AddressInjectable $address = null
    ) {
        $filter = ['email' => $customer->getEmail()];

        $pageCustomerIndex->open();
        $pageCustomerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        \PHPUnit_Framework_Assert::assertTrue(
            $pageCustomerIndexEdit->getCustomerForm()->verifyCustomer($customer, $address),
            'Customer data on edit page(backend) not equals to passed from fixture.'
        );
    }

    /**
     * Text success verify Customer form
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed customer data on edit page(backend) equals to passed from fixture.';
    }
}
