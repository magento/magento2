<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Fixture\Comment;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Source for attribute field.
 */
class Customer extends DataSource
{
    /**
     * Customer Fixtures.
     *
     * @var array
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
     * @constructor
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

        if (isset($this->fixtureData['dataset'])) {
            $customer = $this->fixtureFactory->createByCode('customer', $this->fixtureData);
            /** @var Customer $customer */
            if (!$customer->getId()) {
                $customer->persist();
            }
            $this->customer = $customer;
        }
        $this->data = $customer->getData();

        return parent::getData($key);
    }
}
