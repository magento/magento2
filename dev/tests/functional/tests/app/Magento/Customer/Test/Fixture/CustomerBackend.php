<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
