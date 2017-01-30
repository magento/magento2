<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Fixture\BundleProduct;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Prepare bundle selection items.
 */
class BundleSelections extends DataSource
{
    /**
     * Repository factory instance.
     *
     * @var RepositoryFactory
     */
    protected $repositoryFactory;

    /**
     * Fixture factory instance.
     *
     * @var RepositoryFactory
     */
    protected $fixtureFactory;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $data,
        array $params = []
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $this->data = !isset($data['dataset']) ? $data : [];
        $this->getDataset($data);
        $this->prepareProducts();
    }

    /**
     * Get dataset for a field.
     *
     * @param array $data
     * @return void
     */
    protected function getDataset(array $data)
    {
        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $this->data = $this->repositoryFactory->get($this->params['repository'])->get($data['dataset']);
            if (!empty($data['products'])) {
                $this->data['products'] = [];
                $this->data['products'] = explode('|', $data['products']);
                foreach ($this->data['products'] as $key => $products) {
                    $this->data['products'][$key] = explode(',', $products);
                }
            }
        }
    }

    /**
     * Prepare products for bundle items.
     *
     * @return void
     */
    protected function prepareProducts()
    {
        if (!empty($this->data['products'])) {
            $productsSelections = $this->data['products'];
            $this->data['products'] = [];
            foreach ($productsSelections as $index => $products) {
                $productSelection = [];
                foreach ($products as $key => $product) {
                    if ($product instanceof FixtureInterface) {
                        $productSelection[$key] = $product;
                        continue;
                    }
                    list($fixture, $dataset) = explode('::', $product);
                    $productSelection[$key] = $this->fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
                    $productSelection[$key]->persist();
                    $this->data['bundle_options'][$index]['assigned_products'][$key]['search_data']['name'] =
                        $productSelection[$key]->getName();
                }
                $this->data['products'][] = $productSelection;
            }
        }
    }
}
