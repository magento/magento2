<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * Prepare CustomerId for order list.
 */
class CustomerId extends DataSource
{
    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['customer']) && $data['customer'] instanceof Customer) {
            $this->data = $data['customer'];
            return;
        }
        if (isset($data['dataset'])) {
            $customer = $fixtureFactory->createByCode('customer', ['dataset' => $data['dataset']]);
            if ($customer->hasData('id') === false) {
                $customer->persist();
            }
            $this->data = $customer;
        }
    }

    /**
     * Get customer fixture.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->data;
    }
}
