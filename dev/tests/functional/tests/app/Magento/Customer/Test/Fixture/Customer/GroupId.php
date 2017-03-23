<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture\Customer;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\CustomerGroup;

/**
 * Addresses source for customer fixture.
 */
class GroupId extends DataSource
{
    /**
     * Customer Group fixture.
     *
     * @var array
     */
    protected $customerGroupFixture;

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
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $fixtureFactory->createByCode('customerGroup', ['dataset' => $data['dataset']]);
            if (!$customerGroup->hasData('customer_group_id')) {
                $customerGroup->persist();
            }
            $this->data = $customerGroup->getCustomerGroupCode();
            $this->customerGroupFixture = $customerGroup;
        }
        if (isset($data['customerGroup']) && $data['customerGroup'] instanceof CustomerGroup) {
            $this->data = $data['customerGroup']->getCustomerGroupCode();
            $this->customerGroupFixture = $data['customerGroup'];
        }
        if (isset($data['value'])) {
            $this->data = $data['value'];
        }
    }

    /**
     * Getting customer group fixture.
     *
     * @return array
     */
    public function getCustomerGroup()
    {
        return $this->customerGroupFixture;
    }
}
