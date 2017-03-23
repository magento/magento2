<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * Billing address data.
 */
class BillingAddressId extends DataSource
{
    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        if (isset($data['value'])) {
            $this->data = $data['value'];
            return;
        }
        if (isset($data['dataset'])) {
            $addresses = $fixtureFactory->createByCode('address', ['dataset' => $data['dataset']]);
            $this->data = $addresses->getData();
            $this->data['street'] = [$this->data['street']];
        }
        if (isset($data['billingAddress']) && $data['billingAddress'] instanceof Address) {
            /** @var Address $address */
            $address = $data['billingAddress'];
            $this->data = $address->getData();
            $this->data['street'] = [$this->data['street']];
        }
    }
}
