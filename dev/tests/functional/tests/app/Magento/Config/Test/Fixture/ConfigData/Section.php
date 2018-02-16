<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Level of scope for set.
     * If 'scope_type' = 'website', then 'set_level' MUST be 'website' only.
     *
     * @var string
     */
    private $setLevel = null;

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
            if (isset($this->fixtureData['scope']['scope_type'])) {
                $this->scopeData = $this->fixtureData['scope'];
                $this->scopeType = $this->fixtureData['scope']['scope_type'];
                $this->setLevel = $this->fixtureData['scope']['set_level'];
                $this->prepareScopeData();
                unset($this->fixtureData['scope']);
            }
            $this->data = $this->replacePlaceholders($this->fixtureData);
        }

        return parent::getData($key);
    }

    /**
     * Replace placeholders in parameters array.
     *
     * @param array $data
     * @return array
     */
    private function replacePlaceholders(array $data)
    {
        foreach ($data as &$params) {
            $params = array_map(function ($value) {
                if (is_string($value)) {
                    $value = str_replace(
                        '{{basic_url_to_secure}}',
                        preg_replace('/(http[s]?)/', 'https', $_ENV['app_frontend_url']),
                        $value
                    );
                    $value = str_replace(
                        '{{basic_url_to_unsecure}}',
                        preg_replace('/(http[s]?)/', 'http', $_ENV['app_frontend_url']),
                        $value
                    );
                }
                return $value;
            }, $params);
        }

        return $data;
    }

    /**
     * Prepare scope data.
     *
     * @return void
     * @throws \Exception
     */
    private function prepareScopeData()
    {
        if (isset($this->scopeData['dataset'])) {
            /** @var Store|Website $store */
            $this->scope = $this->fixtureFactory->createByCode(
                $this->scopeType,
                ['dataset' => $this->scopeData['dataset']]
            );
            if (!$this->scope->hasData($this->scopeType . '_id')) {
                $this->scope->persist();
            }
        } elseif (isset($this->scopeData['fixture'])) {
            $this->scope = $this->scopeData['fixture'];
        } else {
            throw new \Exception('Parameters "dataset" and "fixture" aren\'t identify.');
        }

        $this->prepareScope();
    }

    /**
     * Prepare scope.
     *
     * @return void
     * @throws \Exception
     */
    private function prepareScope()
    {
        if ($this->setLevel == self::STORE_CODE && $this->scopeType == self::WEBSITE_CODE) {
            throw new \Exception('Store level can\'t set to ["scope_type" = "website"].');
        } elseif ($this->setLevel == self::WEBSITE_CODE && $this->scopeType == self::STORE_CODE) {
            $this->scopeType = $this->setLevel;
            $this->scope = $this->scope
                ->getDataFieldConfig('group_id')['source']->getStoreGroup()
                ->getDataFieldConfig('website_id')['source']->getWebsite();
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
