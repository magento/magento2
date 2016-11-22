<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Fixture\CmsPage;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Cms Page store id scope.
 */
class StoreId extends DataSource
{
    /**
     * Store fixture.
     *
     * @var \Magento\Store\Test\Fixture\Store
     */
    public $store;

    /**
     * Constructor.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|string $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;

        if (is_array($data) && isset($data['dataset'])) {
            $store = $fixtureFactory->createByCode('store', $data);
            /** @var \Magento\Store\Test\Fixture\Store $store */
            if (!$store->getStoreId()) {
                $store->persist();
            }
            $this->store = $store;
            $this->data = $store->getGroupId() . '/' . $store->getName();
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
