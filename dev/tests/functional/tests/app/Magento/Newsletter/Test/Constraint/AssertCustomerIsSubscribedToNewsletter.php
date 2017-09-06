<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Newsletter\Test\Page\Adminhtml\SubscriberIndex;

/**
 * Class AssertCustomerIsSubscribedToNewsletter
 *
 */
class AssertCustomerIsSubscribedToNewsletter extends AbstractConstraint
{
    /**
     * Assert customer is subscribed to newsletter
     *
     * @param Customer $customer
     * @param SubscriberIndex $subscriberIndex
     * @return void
     */
    public function processAssert(
        Customer $customer,
        SubscriberIndex $subscriberIndex
    ) {
        $filter = [
            'email' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'status' => 'Subscribed',
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
