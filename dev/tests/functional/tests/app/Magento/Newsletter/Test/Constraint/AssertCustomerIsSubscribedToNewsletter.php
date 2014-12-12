<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
