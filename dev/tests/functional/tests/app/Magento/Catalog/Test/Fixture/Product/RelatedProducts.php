<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Repository\RepositoryFactory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Base class for create related products.
 */
class RelatedProducts extends DataSource
{
    /**
     * Products fixture.
     *
     * @var array
     */
    protected $products = [];

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $params,
        array $data = []
    ) {
        $this->params = $params;

        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $datasets = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
            foreach ($datasets as $dataset) {
                list($fixtureCode, $dataset) = explode('::', $dataset);
                $this->products[] = $fixtureFactory->createByCode($fixtureCode, ['dataset' => $dataset]);
            }
        }
        if (isset($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->products[] = $product;
            }
        }

        foreach ($this->products as $product) {
            if (!$product->hasData('id')) {
                $product->persist();
            }

            $this->data[] = [
                'entity_id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
            ];
        }
        if (isset($data['data'])) {
            $this->data = array_replace_recursive($this->data, $data['data']);
        }
    }

    /**
     * Return related products.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
