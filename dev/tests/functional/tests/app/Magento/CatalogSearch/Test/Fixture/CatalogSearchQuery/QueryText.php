<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Data to search for.
 * Possible templates:
 * - {value}
 * - {product}::{product_property_to_search}
 * - {product}::{product_dataSet}::{product_property_to_search}
 */
class QueryText implements FixtureInterface
{
    /**
     * Entity to search.
     *
     * @var InjectableFixture
     */
    protected $product;

    /**
     * Resource data.
     *
     * @var string
     */
    protected $data;

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
            $dataSet = isset($explodeValue[2]) ? $explodeValue[1] : '';
            $searchValue = isset($explodeValue[2]) ? $explodeValue[2] : $explodeValue[1];
            $this->product = $fixtureFactory->createByCode($fixtureCode, ['dataSet' => $dataSet]);
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
     * Persist custom selections products.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data.
     *
     * @param string|null $key
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
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
