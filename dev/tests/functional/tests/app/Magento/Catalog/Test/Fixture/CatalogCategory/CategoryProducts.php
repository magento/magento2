<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogCategory;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class CategoryProducts
 * Prepare products
 */
class CategoryProducts implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array|null
     */
    protected $data;

    /**
     * Return products
     *
     * @var array
     */
    protected $products = [];

    /**
     * Fixture params
     *
     * @var array
     */
    protected $params;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|int $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if (!empty($data['dataSet']) && $data['dataSet'] !== '-') {
            $dataSet = explode(',', $data['dataSet']);
            foreach ($dataSet as $value) {
                $explodeValue = explode('::', $value);
                $product = $fixtureFactory->createByCode($explodeValue[0], ['dataSet' => $explodeValue[1]]);
                if (!$product->getId()) {
                    $product->persist();
                }
                $this->data[] = $product->getName();
                $this->products[] = $product;
            }
        }
    }

    /**
     * Persist attribute options
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key [optional]
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
