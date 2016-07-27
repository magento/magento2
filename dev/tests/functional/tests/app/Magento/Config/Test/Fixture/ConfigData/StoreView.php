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
     * Code of website.
     */
    const WEBSITE_CODE = 'website';

    /**
     * Code of store view.
     */
    const STORE_CODE = 'store';

    /**
     * Store View or Website fixture.
     *
     * @var Store|Website
     */
    private $scope;

    /**
     * Value for set. [website|store]
     *
     * @var string
     */
    private $value;

    /**
     * Fixture Factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Rough fixture field data.
     *
     * @var array|null
     */
    private $fixtureData = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
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
        if ($this->data === null) {
            if (isset($this->fixtureData['dataset'])) {
                /** @var Store $store */
                $store = $this->fixtureFactory->createByCode('store', ['dataset' => $this->fixtureData['dataset']]);
                if (!$store->hasData('store_id')) {
                    $store->persist();
                }

                $this->prepareScope($store);
            } elseif (isset($this->fixtureData['fixture'])) {
                $this->scope = $this->fixtureData['fixture'];
            } else {
                throw new \Exception('Parameters "dataset" and "fixture" aren\'t identify.');
            }

            $this->prepareData();
        }

        return parent::getData($key);
    }

    /**
     * Prepare scope.
     *
     * @param Store $store
     * @return void
     */
    private function prepareScope(Store $store)
    {
        if ($this->fixtureData['value'] == self::STORE_CODE) {
            $this->scope = $store;
        } elseif ($this->fixtureData['value'] == self::WEBSITE_CODE) {
            $this->scope = $this->scope
                ->getDataFieldConfig('group_id')['source']->getStoreGroup()
                ->getDataFieldConfig('website_id')['source']->getWebsite();
        }
    }

    /**
     * Prepare data.
     *
     * @return void
     */
    private function prepareData()
    {
        if ($this->scope instanceof Store) {
            $this->data = $this->scope->getWebsiteId()
                . '/' . $this->scope->getGroupId()
                . '/' . $this->scope->getName();
            $this->value = self::STORE_CODE;
        } elseif ($this->scope instanceof Website) {
            $this->data = $this->scope->getName();
            $this->value = self::WEBSITE_CODE;
        }
    }

    /**
     * Return Store View or Website fixture.
     *
     * @return Store|Website
     */
    public function getScope()
    {
        return $this->scope;
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
