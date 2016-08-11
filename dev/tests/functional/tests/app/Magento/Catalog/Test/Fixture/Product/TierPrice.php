<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Repository\RepositoryFactory;

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
     * Rought fixture field data.
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
     * Return customer group fixture.
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }
}
