<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture\Customer;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Address as AddressFixture;

/**
 * Addresses source for customer fixture.
 */
class Address extends DataSource
{
    /**
     * Customer addresses fixture
     *
     * @var array
     */
    protected $addressesFixture;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['dataset'])) {
            $data['dataset'] = array_map('trim', explode(',', $data['dataset']));
            foreach ($data['dataset'] as $value) {
                /** @var AddressFixture $address*/
                $address = $fixtureFactory->createByCode('address', ['dataset' => $value]);
                $this->data[] = $address->getData();
                $this->addressesFixture[] = $address;
            }
        } elseif (empty($data['dataset']) && !empty($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                /** @var AddressFixture $address */
                $this->data[] = $address->getData();
                $this->addressesFixture[] = $address;
            }
        }
    }

    /**
     * Getting addresses fixture.
     *
     * @return array
     */
    public function getAddresses()
    {
        return $this->addressesFixture;
    }
}
