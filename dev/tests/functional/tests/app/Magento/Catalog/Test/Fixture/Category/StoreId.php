<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Category;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\StoreGroup;

/**
 * Category store id scope.
 */
class StoreId extends DataSource
{
    /**
     * Store fixture.
     *
     * @var Store
     */
    public $store;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['dataset'])) {
            $store = $fixtureFactory->createByCode('store', $data);
            /** @var Store $store */
            if (!$store->getStoreId()) {
                $store->persist();
            }
            $this->store = $store;
            $this->data = $store->getGroupId() . '/' . $store->getName();
        } else if (isset($data['source']) && $data['source'] instanceof Store) {
            $this->store = $data['source'];
            $this->data = $this->store->getGroupId() . '/' . $this->store->getName();
        } else {
            $this->data = $data;
        }
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
}
