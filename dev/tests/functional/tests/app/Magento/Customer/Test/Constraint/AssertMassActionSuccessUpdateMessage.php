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
 * Class AssertMassActionSuccessUpdateMessage
 * Assert update message is appears on customer grid (Customers > All Customers)
 */
class AssertMassActionSuccessUpdateMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const UPDATE_MESSAGE = 'A total of %d record(s) were updated.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert update message is appears on customer grid (Customers > All Customers)
     *
     * @param CustomerInjectable|CustomerInjectable[] $customer
     * @param CustomerIndex $pageCustomerIndex
     * @return void
     */
    public function processAssert($customer, CustomerIndex $pageCustomerIndex)
    {
        $customers = is_array($customer) ? $customer : [$customer];
        $customerCount = count($customers);
        $actualMessage = $pageCustomerIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(sprintf(self::UPDATE_MESSAGE, $customerCount), $actualMessage);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that update message is displayed.';
    }
}
