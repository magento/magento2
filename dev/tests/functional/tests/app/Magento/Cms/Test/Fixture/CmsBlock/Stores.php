<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Fixture\CmsBlock;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Data source for 'stores' field.
 *
 * Data keys:
 *  - dataSet
 */
class Stores implements FixtureInterface
{
    /**
     * Array with store names.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Array with store fixtures.
     *
     * @var array
     */
    protected $stores;

    /**
     * Data set configuration settings.
     *
     * @var array
     */
    protected $params;

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
        if (isset($data['dataSet'])) {
            $dataSets = is_array($data['dataSet']) ? $data['dataSet'] : [$data['dataSet']];
            foreach ($dataSets as $dataSet) {
                /** @var \Magento\Store\Test\Fixture\Store $store */
                $store = $fixtureFactory->createByCode('store', ['dataSet' => $dataSet]);
                if (!$store->hasData('store_id')) {
                    $store->persist();
                }
                $this->stores[] = $store;
                $this->data[] = $store->getName() == 'All Store Views'
                    ? $store->getName()
                    : $store->getGroupId() . '/' . $store->getName();
            }
        }
    }

    /**
     * Persist custom selections store view.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string|null $key [optional]
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
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

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
