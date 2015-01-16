<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class Associated
 * Grouped selections preset
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class Associated implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        $this->data = isset($data['preset']) ? $this->getPreset($data['preset']) : $data;

        $this->data['products'] = (isset($data['products']) && !is_array($data['products']))
            ? explode(',', $data['products'])
            : $this->data['products'];

        foreach ($this->data['products'] as $key => $product) {
            if (!($product instanceof FixtureInterface)) {
                list($fixture, $dataSet) = explode('::', $product);
                /** @var $productFixture InjectableFixture */
                $product = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
            }
            if (!$product->hasData('id')) {
                $product->persist();
            }
            $this->data['products'][$key] = $product;
        }

        $assignedProducts = & $this->data['assigned_products'];
        foreach (array_keys($assignedProducts) as $key) {
            $assignedProducts[$key]['name'] = $this->data['products'][$key]->getName();
            $assignedProducts[$key]['id'] = $this->data['products'][$key]->getId();
            $assignedProducts[$key]['position'] = $key + 1;
        }
    }

    /**
     * Persists prepared data into application
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
     * @return mixed
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
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Preset array
     *
     * @param string $name
     * @return mixed|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'defaultSimpleProduct' => [
                'assigned_products' => [
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 1,
                    ],
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 2,
                    ],
                ],
                'products' => [
                    'catalogProductSimple::default',
                    'catalogProductSimple::100_dollar_product',
                ],
            ],
            'defaultSimpleProduct_without_qty' => [
                'assigned_products' => [
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 0,
                    ],
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 0,
                    ],
                ],
                'products' => [
                    'catalogProductSimple::default',
                    'catalogProductSimple::100_dollar_product',
                ],
            ],
            'defaultSimpleProduct_with_specialPrice' => [
                'assigned_products' => [
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 1,
                    ],
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 2,
                    ],
                ],
                'products' => [
                    'catalogProductSimple::withSpecialPrice',
                    'catalogProductSimple::withSpecialPrice',
                ],
            ],
            'defaultVirtualProduct' => [
                'assigned_products' => [
                    [
                        'id' => '%id%',
                        'name' => '%item1_virtual::getProductName%',
                        'position' => '%position%',
                        'qty' => 1,
                    ],
                    [
                        'id' => '%id%',
                        'name' => '%item1_virtual::getProductName%',
                        'position' => '%position%',
                        'qty' => 2,
                    ],
                ],
                'products' => [
                    'catalogProductVirtual::default',
                    'catalogProductVirtual::50_dollar_product',
                ],
            ],
            'three_simple_products' => [
                'assigned_products' => [
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 17,
                    ],
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 36,
                    ],
                    [
                        'id' => '%id%',
                        'name' => '%item1_simple::getProductName%',
                        'position' => '%position%',
                        'qty' => 20,
                    ],
                ],
                'products' => [
                    'catalogProductSimple::default',
                    'catalogProductSimple::40_dollar_product',
                    'catalogProductSimple::100_dollar_product',
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
