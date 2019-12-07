<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Repository\RepositoryFactory;
use Magento\Store\Test\Fixture\Website;

/**
 * TierPrice data source.
 *
 * Data keys:
 *  - dataset
 */
class TierPrice extends DataSource
{
    /**
     * Customer group fixture array.
     *
     * @var array
     */
    private $customerGroups;

    /**
     * Website fixture.
     *
     * @var \Magento\Store\Test\Fixture\Website
     */
    private $website;

    /**
     * Repository Factory instance.
     *
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * Fixture Factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Rough fixture field data.
     *
     * @var array
     */
    private $fixtureData = null;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     * @throws \Exception
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $params,
        $data = []
    ) {
        $this->repositoryFactory = $repositoryFactory;
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
        if (!isset($this->fixtureData['dataset'])) {
            throw new \Exception("Data must be set");
        }
        $this->data = $this->repositoryFactory->get($this->params['repository'])->get($this->fixtureData['dataset']);
        if (!empty($this->fixtureData['data']['website'])) {
            $this->prepareWebsite();
        }
        foreach ($this->data as $key => $item) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $this->fixtureFactory->createByCode(
                'customerGroup',
                ['dataset' => $item['customer_group']['dataset']]
            );
            if (!$customerGroup->hasData('customer_group_id')) {
                $customerGroup->persist();
            }
            $this->data[$key]['customer_group'] = $customerGroup->getCustomerGroupCode();
            $this->customerGroups[$key] = $customerGroup;
        }

        return parent::getData($key);
    }

    /**
     * Prepare website data.
     *
     * @return void
     */
    private function prepareWebsite()
    {
        if (is_array($this->fixtureData['data']['website'])
            && isset($this->fixtureData['data']['website']['dataset'])) {
            /** @var Website $website */
            $this->website = $this->fixtureFactory->createByCode(
                'website',
                ['dataset' => $this->fixtureData['data']['website']['dataset']]
            );
            $this->website->persist();
        } else {
            $this->website = $this->fixtureData['data']['website'];
        }

        $this->fixtureData['data']['website'] = $this->website->getCode();
        foreach ($this->data as $key => $data) {
            $this->data[$key] = array_merge($data, $this->fixtureData['data']);
        }
    }

    /**
     * Return customer group fixture.
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * Return website fixture.
     *
     * @return \Magento\Store\Test\Fixture\Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
