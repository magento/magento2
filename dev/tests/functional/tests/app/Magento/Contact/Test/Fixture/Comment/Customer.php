<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Fixture\Comment;

use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Customer comment on contact page.
 */
class Customer extends DataSource
{
    /**
     * Customer Fixture.
     *
     * @var CustomerFixture
     */
    private $customer;

    /**
     * Fixture Factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|int $data
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        $data = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $this->data = $data;
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return mixed
     * @throws \Exception
     */
    public function getData($key = null)
    {
        if (empty($this->data)) {
            throw new \Exception("Data must be set");
        }

        if (isset($this->data['dataset']) && !$this->customer) {
            /** @var CustomerFixture $customer */
            $customer = $this->fixtureFactory->createByCode('customer', $this->data);

            if (!$customer->getId()) {
                $customer->persist();
            }

            $this->customer = $customer;
            $this->data = [
                'firstname' => $customer->getFirstname(),
                'email' => $customer->getEmail(),
            ];

            if ($customer->hasData('telephone')) {
                $this->data['telephone'] = $customer->getData('telephone');
            }
        }

        return parent::getData($key);
    }

    /**
     * Return customer.
     *
     * @return CustomerFixture
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
