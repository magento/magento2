<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture\Customer;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\Website;

/**
 * Prepare website.
 */
class WebsiteId extends DataSource
{
    /**
     * Store Fixture.
     *
     * @var Store
     */
    private $store;

    /**
     * Website.
     *
     * @var Website
     */
    private $website;

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
     * @param array $data
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

        if (isset($this->fixtureData['website'])) {
            $this->website = $this->fixtureData['website'];
            $this->data = $this->fixtureData['website']->getName();
        } else {
            if (isset($this->fixtureData['dataset'])) {
                $store = $this->fixtureFactory->createByCode('store', $this->fixtureData);

                if (!$store->getStoreId()) {
                    $store->persist();
                }

                $website = $store->getDataFieldConfig('group_id')['source']
                    ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite();

                $this->data = $website->getName();
                $this->website = $website;
                $this->store = $store;
            }
        }

        return parent::getData($key);
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

    /**
     * Return website code.
     *
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
