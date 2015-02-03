<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;

use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Stores
 * Prepare Stores
 */
class Stores implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Store fixture
     *
     * @var Store[]
     */
    public $stores;

    /**
     * Constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        if (isset($data['dataSet'])) {
            foreach ($data['dataSet'] as $store) {
                $store = $fixtureFactory->createByCode('store', ['dataSet' => $store]);
                /** @var Store $store */
                if (!$store->getStoreId()) {
                    $store->persist();
                }
                $this->stores[] = $store;
                $this->data[] = $store->getGroupId() . '/' . $store->getName();
            }
        }
    }

    /**
     * Persist stores
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
     * @param string|null $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
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
     * Return array
     *
     * @return Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }
}
