<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Data to search for.
 * Possible templates:
 * - {value}
 * - {product}::{product_property_to_search}
 * - {product}::{product_dataset}::{product_property_to_search}
 */
class QueryText extends DataSource
{
    /**
     * Entity to search.
     *
     * @var InjectableFixture
     */
    protected $product;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        $explodeValue = explode('::', $data['value']);
        if (!empty($explodeValue) && count($explodeValue) > 1) {
            $fixtureCode = $explodeValue[0];
            $dataset = isset($explodeValue[2]) ? $explodeValue[1] : '';
            $searchValue = isset($explodeValue[2]) ? $explodeValue[2] : $explodeValue[1];
            $this->product = $fixtureFactory->createByCode($fixtureCode, ['dataset' => $dataset]);
            if (!$this->product->hasData('id')) {
                $this->product->persist();
            }
            if ($this->product->hasData($searchValue)) {
                $getProperty = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $searchValue)));
                $this->data = $this->product->$getProperty();
            } else {
                $this->data = $searchValue;
            }
        } else {
            $this->data = strval($data['value']);
        }
    }

    /**
     * Get product fixture to search.
     *
     * @return InjectableFixture
     */
    public function getProduct()
    {
        return $this->product;
    }
}
