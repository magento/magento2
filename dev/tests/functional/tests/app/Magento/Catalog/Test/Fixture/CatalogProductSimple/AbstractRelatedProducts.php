<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AbstractRelatedProducts
 * Base class for create related products
 */
class AbstractRelatedProducts implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Data of the created products
     *
     * @var array
     */
    protected $data = [];

    /**
     * Products fixture
     *
     * @var array
     */
    protected $products = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['presets'])) {
            $presets = array_map('trim', explode(',', $data['presets']));
            foreach ($presets as $preset) {
                list($fixtureCode, $dataSet) = explode('::', $preset);

                /** @var CatalogProductSimple $product */
                $product = $fixtureFactory->createByCode($fixtureCode, ['dataSet' => $dataSet]);
                if (!$product->hasData('id')) {
                    $product->persist();
                }

                $this->products[] = $product;
                $this->data[] = [
                    'entity_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                ];
            }
        }

        if (isset($data['data'])) {
            $this->data = array_replace_recursive($this->data, $data['data']);
        }
    }

    /**
     * Persist related products
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
     * @param string|null $key
     * @return array
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
     * Return related products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
