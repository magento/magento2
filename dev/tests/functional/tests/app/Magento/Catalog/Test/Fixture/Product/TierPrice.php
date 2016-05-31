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
        $this->params = $params;
        if (!isset($data['dataset'])) {
            throw new \Exception("Data must be set");
        }
        $this->data = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
        foreach ($this->data as $key => $item) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $fixtureFactory->createByCode(
                'customerGroup',
                ['dataset' => $item['customer_group']['dataset']]
            );
            if (!$customerGroup->hasData('customer_group_id')) {
                $customerGroup->persist();
            }
            $this->data[$key]['customer_group'] = $customerGroup->getCustomerGroupCode();
            $this->customerGroups[$key] = $customerGroup;
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
}
