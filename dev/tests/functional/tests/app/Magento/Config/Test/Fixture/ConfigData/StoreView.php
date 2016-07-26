<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Fixture\ConfigData;

use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * Prepare Store View entity.
 */
class StoreView extends DataSource
{
    /**
     * Store View or Website fixture.
     *
     * @var Store|Website
     */
    private $storeViewEntity;

    /**
     * Value for set. [website|store]
     *
     * @var string
     */
    private $value;

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
            /** @var Store $store */
            $store = $fixtureFactory->createByCode('store', ['dataset' => $data['dataset']]);
            if (!$store->hasData('store_id')) {
                $store->persist();
            }
            $this->storeViewEntity = $store;
            $this->value = $data['value'];
            $this->data = $store->getWebsiteId();
            if ($data['value'] == 'store') {
                $this->data .= '/' . $store->getGroupId() . '/' . $store->getName();
            } elseif ($data['value'] == 'website') {
                $this->storeViewEntity = $this->storeViewEntity
                    ->getDataFieldConfig('group_id')['source']->getStoreGroup()
                    ->getDataFieldConfig('website_id')['source']->getWebsite();
            }
        } else {
            $this->data = null;
        }
    }

    /**
     * Return Store View entity fixture.
     *
     * @return Store|Website
     */
    public function getStoreViewEntity()
    {
        return $this->storeViewEntity;
    }

    /**
     * Get code of store view entity to apply [website|store].
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
