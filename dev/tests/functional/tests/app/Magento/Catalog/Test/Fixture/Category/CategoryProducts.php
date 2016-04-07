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
                $explodeValue = explode('::', $value);
                $product = $fixtureFactory->createByCode($explodeValue[0], ['dataset' => $explodeValue[1]]);
                if (!$product->getId()) {
                    $product->persist();
                }
                $this->data[] = $product->getName();
                $this->products[] = $product;
            }
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
