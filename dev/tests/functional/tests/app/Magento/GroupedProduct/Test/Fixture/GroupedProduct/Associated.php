<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Fixture\GroupedProduct;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Grouped selections sources.
 */
class Associated extends DataSource
{
    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $data,
        array $params = []
    ) {
        $this->params = $params;
        $this->data = isset($data['dataset'])
            ? $repositoryFactory->get($this->params['repository'])->get($data['dataset'])
            : $data;

        $this->data['products'] = (isset($data['products']) && !is_array($data['products']))
            ? explode(',', $data['products'])
            : $this->data['products'];

        foreach ($this->data['products'] as $key => $product) {
            if (!($product instanceof FixtureInterface)) {
                list($fixture, $dataset) = explode('::', $product);
                /** @var $productFixture InjectableFixture */
                $product = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
            }
            if (!$product->hasData('id')) {
                $product->persist();
            }
            $this->data['products'][$key] = $product;
        }

        $assignedProducts = &$this->data['assigned_products'];
        foreach (array_keys($assignedProducts) as $key) {
            $assignedProducts[$key]['name'] = $this->data['products'][$key]->getName();
            $assignedProducts[$key]['id'] = $this->data['products'][$key]->getId();
            $assignedProducts[$key]['position'] = $key + 1;
        }
    }
}
