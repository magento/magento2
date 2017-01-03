<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\Website;
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
     * Website fixture.
     *
     * @var Website
     */
    private $website;

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

        if (isset($data['store'])) {
            $this->store = $data['store'];
            $website = $data['store']->getDataFieldConfig('group_id')['source']
                ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();
            $this->website = $website;
            $this->data = $data['store']->getName();
        } else {
            if ($storeData) {
                $store = $fixtureFactory->createByCode('store', $storeData);
                /** @var Store $store */
                if (!$store->getStoreId()) {
                    $store->persist();
                }
                if (isset($store->getData()['group_id'])) {
                    $website = $store->getDataFieldConfig('group_id')['source']
                        ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();
                    $this->website = $website;
                }

                $this->store = $store;
                $this->data = $store->getName();
            }
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

    /**
     * Return Website fixture.
     *
     * @return Website|null
     */
    public function getWebsite()
    {
        if (isset($this->website)) {
            return $this->website;
        }
        return null;
    }
}
