<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;

use Magento\Mtf\Fixture\DataSource;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare Stores.
 */
class Stores extends DataSource
{
    /**
     * Store fixture.
     *
     * @var Store[]
     */
    public $stores;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        if (isset($data['dataset'])) {
            foreach ($data['dataset'] as $store) {
                $store = $fixtureFactory->createByCode('store', ['dataset' => $store]);
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
     * Return array.
     *
     * @return Store[]
     */
    public function getStores()
    {
        return $this->stores;
    }
}
