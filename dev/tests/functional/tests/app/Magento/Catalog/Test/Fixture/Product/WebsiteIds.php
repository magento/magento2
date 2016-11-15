<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
            if (is_array($dataset) && isset($dataset['dataset'])) {
                $store = $this->fixtureFactory->createByCode('store', $dataset);

                if (!$store->getStoreId()) {
                    $store->persist();
                }

                $website = $store->getDataFieldConfig('group_id')['source']
                    ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();

                $this->data[] = $website->getName();
                $this->websites[] = $website;
                $this->stores[] = $store;
            } elseif ($dataset instanceof Store) {
                if (!$dataset->getStoreId()) {
                    $dataset->persist();
                }

                $website = $dataset->getDataFieldConfig('group_id')['source']
                    ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();

                $this->data[] = $website->getName();
                $this->websites[] = $website;
                $this->stores[] = $dataset;
            }
        }

        return parent::getData($key);
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
