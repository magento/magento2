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
 * Prepare Section entity.
 */
class Section extends DataSource
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
     * Scope type. [website|store]
     *
     * @var string
     */
    private $scopeType;

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
     * Scope data.
     *
     * @var array|null
     */
    private $scopeData = null;

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
     */
    public function getData($key = null)
    {
        if ($this->data === null) {
            if (isset($this->fixtureData['scope'])) {
                $this->scopeData = $this->fixtureData['scope'];
                $this->prepareScopeData();
                $this->prepareScopeType();
                unset($this->fixtureData['scope']);
            }
            $this->data = $this->fixtureData;
        }

        return parent::getData($key);
    }

    /**
     * Prepare scope data.
     *
     * @return void
     * @throws \Exception
     */
    public function prepareScopeData()
    {
        if (isset($this->scopeData['dataset'])) {
            /** @var Store $store */
            $store = $this->fixtureFactory->createByCode('store', ['dataset' => $this->scopeData['dataset']]);
            if (!$store->hasData('store_id')) {
                $store->persist();
            }

            $this->setScope($store);
        } elseif (isset($this->scopeData['fixture'])) {
            $this->scope = $this->scopeData['fixture'];
        } else {
            throw new \Exception('Parameters "dataset" and "fixture" aren\'t identify.');
        }
    }

    /**
     * Prepare scope.
     *
     * @param Store $store
     * @return void
     */
    private function setScope(Store $store)
    {
        if ($this->scopeData['value'] == self::STORE_CODE) {
            $this->scope = $store;
        } elseif ($this->scopeData['value'] == self::WEBSITE_CODE) {
            $this->scope = $this->scope
                ->getDataFieldConfig('group_id')['source']->getStoreGroup()
                ->getDataFieldConfig('website_id')['source']->getWebsite();
        }
    }

    /**
     * Prepare scope type.
     *
     * @return void
     */
    private function prepareScopeType()
    {
        if ($this->scope instanceof Store) {
            $this->scopeType = self::STORE_CODE;
        } elseif ($this->scope instanceof Website) {
            $this->scopeType = self::WEBSITE_CODE;
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
     * Get get scope type [website|store].
     *
     * @return string
     */
    public function getScopeType()
    {
        return $this->scopeType;
    }
}
