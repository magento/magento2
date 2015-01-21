<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Fixture\Product;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Category
 *
 * Data keys:
 *  - preset (Product options preset name)
 */
class Category implements FixtureInterface
{
    /**
     * @var \Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param mixed $data
     * @param array $params
     * @param bool $persist
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        $data,
        array $params = [],
        $persist = false
    ) {
        $this->fixtureFactory = $fixtureFactory;

        $this->data = $data;

        if (isset($this->data['products'])) {
            $products = explode(',', $this->data['products']);
            $this->data['products'] = [];
            foreach ($products as $key => $product) {
                list($fixture, $dataSet) = explode('::', $product);
                $this->data['products'][$key] = $this->fixtureFactory
                    ->createByCode($fixture, ['dataSet' => $dataSet]);
            }
        }

        $this->data['preset'] = $this->getPreset($this->data['preset']);

        $this->params = $params;
        if ($persist) {
            $this->persist();
        }
    }

    /**
     * Persist bundle selections products
     *
     * @return void
     */
    public function persist()
    {
        if (isset($this->data['products'])) {
            foreach ($this->data['products'] as $product) {
                /** @var $product FixtureInterface */
                $product->persist();
            }
        }
    }

    /**
     * Return prepared data set
     *
     * @param $key [optional]
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
     * @param $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function getPreset($name)
    {
        $presets = [
            'simple_category' => [
                'name' => 'Simple With Category',
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
