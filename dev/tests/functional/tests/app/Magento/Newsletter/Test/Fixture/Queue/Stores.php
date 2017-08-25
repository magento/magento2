<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Fixture\Queue;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Data source for 'stores' field.
 *
 * Data keys:
 *  - dataset
 */
class Stores extends DataSource
{
    /**
     * Array with store fixtures.
     *
     * @var array
     */
    protected $stores;

    /**
     * Create custom Store if we have block with custom store view.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataset'])) {
            $datasets = is_array($data['dataset']) ? $data['dataset'] : [$data['dataset']];
            foreach ($datasets as $dataset) {
                /** @var \Magento\Store\Test\Fixture\Store $store */
                $store = $fixtureFactory->createByCode('store', ['dataset' => $dataset]);
                if (!$store->hasData('store_id')) {
                    $store->persist();
                }
                $this->stores[] = $store;
                $this->data[] = $store->getGroupId() . '/' . $store->getName();
            }
        }
    }

    /**
     * Return stores.
     *
     * @return array
     */
    public function getStores()
    {
        return $this->stores;
    }
}
