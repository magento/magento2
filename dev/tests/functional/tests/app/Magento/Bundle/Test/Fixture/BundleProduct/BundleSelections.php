<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $this->params = $params;
        $this->data = !isset($data['dataset']) ? $data : [];

        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $this->data = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
            if (!empty($data['products'])) {
                $this->data['products'] = [];
                $this->data['products'] = explode('|', $data['products']);
                foreach ($this->data['products'] as $key => $products) {
                    $this->data['products'][$key] = explode(',', $products);
                }
            }
        }

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
                    $productSelection[$key] = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
                    $productSelection[$key]->persist();
                    $this->data['bundle_options'][$index]['assigned_products'][$key]['search_data']['name'] =
                        $productSelection[$key]->getName();
                }
                $this->data['products'][] = $productSelection;
            }
        }
    }
}
