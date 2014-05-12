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

namespace Magento\Newsletter\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Newsletter\Test\Page\Adminhtml\SubscriberIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerIsSubscribedToNewsletter
 *
 */
class AssertCustomerIsSubscribedToNewsletter extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert customer is subscribed to newsletter
     *
     * @param CustomerInjectable $customer
     * @param SubscriberIndex $subscriberIndex
     * @return void
     */
    public function processAssert(
        CustomerInjectable $customer,
        SubscriberIndex $subscriberIndex
    ) {
        $filter = [
            'email' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'status' => 'Subscribed'
        ];

        $subscriberIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $subscriberIndex->getSubscriberGrid()->isRowVisible($filter),
            'Customer with email \'' . $customer->getEmail() . '\' is absent in Newsletter Subscribers grid.'
        );
    }

    /**
     * Text of successful customer's subscription to newsletter
     *
     * @return string
     */
    public function toString()
    {
        return "Customer is subscribed to newsletter";
    }
}
