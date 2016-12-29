<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|string $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        $this->fixtureFactory = $fixtureFactory;
        $this->processData($data);
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
     * @param array|string $data
     */
    private function processData($data)
    {
        if (is_array($data) && isset($data['dataset'])) {
            $store = $this->fixtureFactory->createByCode('store', $data);
            /** @var Store $store */
            if (!$store->getStoreId()) {
                $store->persist();
            }
            $this->store = $store;
            $this->data = $store->getGroupId() . '/' . $store->getName();
        } else {
            $this->data = $data;
        }
    }
}
