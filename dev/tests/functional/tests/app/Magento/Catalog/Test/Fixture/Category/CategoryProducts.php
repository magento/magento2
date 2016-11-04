<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Category;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare products.
 */
class CategoryProducts extends DataSource
{
    /**
     * Return products.
     *
     * @var array
     */
    protected $products = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|int $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if (!empty($data['dataset']) && $data['dataset'] !== '-') {
            $dataset = array_map('trim', explode(',', $data['dataset']));
            foreach ($dataset as $value) {
                list($fixtureCode, $dataset) = explode('::', $value);
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
            $this->data[] = $product->getName();
        }
    }

    /**
     * Return products.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
