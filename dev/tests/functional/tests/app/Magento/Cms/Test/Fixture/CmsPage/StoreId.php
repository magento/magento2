<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Fixture\CmsPage;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Store;

/**
 * Cms Page store id scope.
 */
class StoreId extends DataSource
{
    /**
     * Store fixture.
     *
     * @var Store
     */
    private $store;

    /**
     * Fixture factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Data which have been passed from the variation.
     *
     * @var mixed
     */
    private $variationData;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|string $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        $this->fixtureFactory = $fixtureFactory;
        $this->variationData = $data;
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return mixed
     */
    public function getData($key = null)
    {
        if (null === $this->data) {
            $this->processData();
        }
        return parent::getData($key);
    }

    /**
     * Return Store fixture.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Process input data.
     *
     * @return void
     */
    private function processData()
    {
        if (is_array($this->variationData) && isset($this->variationData['dataset'])) {
            $store = $this->fixtureFactory->createByCode('store', $this->variationData);
            /** @var Store $store */
            if (!$store->getStoreId()) {
                $store->persist();
            }
            $this->store = $store;
            $this->data = $store->getGroupId() . '/' . $store->getName();
        } else {
            $this->data = $this->variationData;
        }
    }
}
