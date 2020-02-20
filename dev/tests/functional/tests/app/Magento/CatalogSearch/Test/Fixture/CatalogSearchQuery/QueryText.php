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
     * @var InjectableFixture[]
     */
    private $products;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        $this->data = array_key_exists('search_query', $data) ? $data['search_query'] : null;

        $this->products = $this->createProducts($fixtureFactory, (array)$data['value']);
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $productsData
     * @return InjectableFixture[]
     */
    private function createProducts(FixtureFactory $fixtureFactory, $productsData)
    {
        $products = [];
        foreach ($productsData as $productStringData) {
            $productData = explode('::', $productStringData);
            if (!empty($productData) && count($productData) > 1) {
                $product = $this->createProduct($fixtureFactory, $productData);

                $searchValue = isset($productData[2]) ? $productData[2] : $productData[1];
                if ($this->data === null) {
                    if ($product->hasData($searchValue)) {
                        $getProperty = 'get' . str_replace('_', '', ucwords($searchValue, '_'));
                        $this->data = $product->$getProperty();
                    } else {
                        $this->data = $searchValue;
                    }
                }

                $products[] = $product;
            } elseif ($this->data === null) {
                $this->data = (string)$productData;
            }
        }

        return $products;
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param $productData
     * @return InjectableFixture
     */
    private function createProduct(FixtureFactory $fixtureFactory, $productData)
    {
        $fixtureCode = $this->getProductFixtureCode($productData);
        $dataset = $this->getProductDataSet($productData);
        $product = $fixtureFactory->createByCode($fixtureCode, ['dataset' => $dataset]);
        if (!$product->hasData('id')) {
            $product->persist();
        }

        return $product;
    }

    /**
     * @param $productData
     * @return string
     */
    private function getProductFixtureCode($productData)
    {
        $fixtureCode = $productData[0];

        return $fixtureCode;
    }

    /**
     * @param $productData
     * @return string
     */
    private function getProductDataSet($productData)
    {
        return (isset($productData[2]) || null !== $this->data) ? $productData[1] : '';
    }

    /**
     * Get product fixture to search.
     *
     * @return InjectableFixture
     */
    public function getFirstProduct()
    {
        return reset($this->products);
    }

    /**
     * Get product fixture to search.
     *
     * @return InjectableFixture[]
     */
    public function getProducts()
    {
        return $this->products;
    }
}
