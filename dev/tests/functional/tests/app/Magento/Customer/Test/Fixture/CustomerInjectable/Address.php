<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture\CustomerInjectable;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Address
 * Addresses source for customer fixture
 */
class Address implements FixtureInterface
{
    /**
     * Source data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Source parameters
     *
     * @var array
     */
    protected $params;

    /**
     * Customer addresses fixture
     *
     * @var array
     */
    protected $addressesFixture;

    /**
     * Source constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['presets'])) {
            $data['presets'] = explode(',', $data['presets']);
            foreach ($data['presets'] as $value) {
                /** @var AddressInjectable $fixture */
                $addresses = $fixtureFactory->createByCode('addressInjectable', ['dataSet' => $value]);
                $this->data[] = $addresses->getData();
                $this->addressesFixture[] = $addresses;
            }
        } elseif (empty($data['presets']) && !empty($data['addresses'])) {
            foreach ($data['addresses'] as $addresses) {
                /** @var AddressInjectable $addresses */
                $this->data[] = $addresses->getData();
                $this->addressesFixture[] = $addresses;
            }
        }
    }

    /**
     * Persists prepared data into application
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param int|null $key [optional]
     * @return array
     */
    public function getData($key = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Getting addresses fixture
     *
     * @return array
     */
    public function getAddresses()
    {
        return $this->addressesFixture;
    }
}
