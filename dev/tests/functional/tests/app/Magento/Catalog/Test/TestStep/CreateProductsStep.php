<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class CreateProductsStep
 * Create products using handler
 */
class CreateProductsStep implements TestStepInterface
{
    /**
     * Products names in data set
     *
     * @var string
     */
    protected $products;

    /**
     * Product data
     *
     * @var array
     */
    protected $data;

    /**
     * Factory for Fixtures
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing step properties
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $products
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, $products, array $data = [])
    {
        $this->products = $products;
        $this->data = $data;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create products
     *
     * @return array
     */
    public function run()
    {
        $products = [];
        $productsDataSets = explode(',', $this->products);
        foreach ($productsDataSets as $key => $productDataSet) {
            $productDataSet = explode('::', $productDataSet);
            $fixtureClass = $productDataSet[0];
            $dataset = isset($productDataSet[1]) ? $productDataSet[1] : '';
            $data = isset($this->data[$key]) ? $this->data[$key] : [];
            /** @var FixtureInterface[] $products */
            $products[$key] = $this->fixtureFactory->createByCode(
                trim($fixtureClass),
                ['dataset' => trim($dataset), 'data' => $data]
            );
            if ($products[$key]->hasData('id') === false) {
                $products[$key]->persist();
            }
        }

        return ['products' => $products];
    }
}
