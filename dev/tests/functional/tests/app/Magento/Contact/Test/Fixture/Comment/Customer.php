<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Fixture\Comment;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Customer comment on contact page.
 */
class Customer extends DataSource
{
    /**
     * Customer Fixture.
     *
     * @var FixtureInterface
     */
    private $customer;

    /**
     * Fixture Factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Rought fixture field data.
     *
     * @var array
     */
    private $fixtureData = null;

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
        $this->fixtureData = $data;
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
        if (empty($this->fixtureData)) {
            throw new \Exception("Data must be set");
        }

        if (isset($this->fixtureData['dataset']) && !$this->customer) {
            $customer = $this->fixtureFactory->createByCode('customer', $this->fixtureData);

            if (!$customer->getId()) {
                $customer->persist();
            }

            $this->customer = $customer;
            $this->data = [
                'firstname' => $customer->getFirstname(),
                'email' => $customer->getEmail(),
                'telephone' => $customer->getEmail()
            ];

        }

        return parent::getData($key);
    }

    /**
     * Return customer.
     *
     * @return FixtureInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
