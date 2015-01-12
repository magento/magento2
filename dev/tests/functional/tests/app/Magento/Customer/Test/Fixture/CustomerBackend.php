<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture;

use Mtf\Factory\Factory;

/**
 * Customer in Backend
 *
 */
class CustomerBackend extends Customer
{
    /**
     * Create customer via backend
     */
    public function persist()
    {
        Factory::getApp()->magentoCustomerCreateCustomerBackend($this);
    }
}
