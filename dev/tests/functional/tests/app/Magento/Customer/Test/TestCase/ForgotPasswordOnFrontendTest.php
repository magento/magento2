<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Precondition:
 * 1. Customer is created.
 *
 * @group Customer_(CS)
 */
class ForgotPasswordOnFrontendTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Create customer.
     *
     * @param Customer $customer
     * @return void
     */
    public function test(Customer $customer)
    {
        // Precondition
        $customer->persist();
    }
}
