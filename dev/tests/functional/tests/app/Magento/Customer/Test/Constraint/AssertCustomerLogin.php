<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer successfully log in.
 */
class AssertCustomerLogin extends AbstractConstraint
{
    /**
     * Assert that customer successfully logs in.
     *
     * @param CmsIndex $cmsIndex
     * @param Customer $customer
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getLinksBlock()->isAuthorizationVisible(),
            "Authorisation link is visible after Login attempt."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer is successfully logged in.";
    }
}
