<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Fixture\SynonymGroup;

use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * Class ScopeId
 */
class ScopeId extends DataSource
{
    /**
     * Return store.
     *
     * @var Store
     */
    protected $store = null;

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
            $store = $fixtureFactory->createByCode('store', ['dataset' => $data['dataset']]);
            if (!$store->hasData('store_id')) {
                $store->persist();
            }
            $this->store = $store;
            $this->data = $store->getName();
        } else {
            $this->data = null;
        }
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }
}
