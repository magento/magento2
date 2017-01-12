<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Store;

/**
 * Prepare websites.
 */
class WebsiteIds extends DataSource
{
    /**
     * Store Fixtures.
     *
     * @var array
     */
    private $stores = [];

    /**
     * Websites.
     *
     * @var array
     */
    private $websites = [];

    /**
     * Fixture Factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Rought fixture field data.
     *
     * @var array
     */
    private $fixtureData = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|int $data
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        $data = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $this->fixtureData = $data;
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return mixed
     * @throws \Exception
     */
    public function getData($key = null)
    {
        if (empty($this->fixtureData)) {
            throw new \Exception("Data must be set");
        }

        foreach ($this->fixtureData as $dataset) {
            if (is_array($dataset)) {
                $this->processStore($dataset);
            } elseif ($dataset instanceof Store) {
                $this->processStore($dataset);
            } elseif (isset($dataset['websites'])) {
                foreach ($dataset['websites'] as $website) {
                    $this->websites[] = $website;
                }
            }
        }

        return parent::getData($key);
    }

    /**
     * Add website by dataset.
     *
     * @param array $dataset
     * @return void
     */
    private function processStore(array $dataset)
    {
        if (isset($dataset['store'])) {
            $website = $dataset['store']->getDataFieldConfig('group_id')['source']
                ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();
            $this->data[] = $website->getName();
            $this->websites[] = $website;
            $this->stores[] = $dataset['store'];
        } elseif (isset($dataset['dataset'])) {
            $store = $this->fixtureFactory->createByCode('store', $dataset);
            !$store->getStoreId() ? : $store->persist();
            $website = $store->getDataFieldConfig('group_id')['source']
                ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();
            $this->data[] = $website->getName();
            $this->websites[] = $website;
            $this->stores[] = $store;
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

    /**
     * Return website codes.
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->websites;
    }
}
