<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * Prepare StoreId for Store Group.
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
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;

        $storeData =  isset($data['dataset']) ? ['dataset' => $data['dataset']] : [];
        if (isset($data['data'])) {
            $storeData = array_replace_recursive($storeData, $data);
        }

        if ($storeData) {
            $store = $fixtureFactory->createByCode('store', $storeData);
            /** @var Store $store */
            if (!$store->getStoreId()) {
                $store->persist();
            }
            $this->store = $store;
            $this->data = $store->getName();
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
