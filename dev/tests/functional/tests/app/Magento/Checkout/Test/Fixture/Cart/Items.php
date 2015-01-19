<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Fixture\Cart;

use Mtf\Fixture\FixtureInterface;
use Mtf\ObjectManager;

/**
 * Class Item
 * Data for verify cart item block on checkout page
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Items implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data = [];

    /**
     * List fixture products
     *
     * @var FixtureInterface[]
     */
    protected $products;

    /**
     * @constructor
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        $this->products = isset($data['products']) ? $data['products'] : [];

        foreach ($this->products as $product) {
            $classItem = 'Magento\\' . $this->getModuleName($product) . '\Test\Fixture\Cart\Item';
            $item = ObjectManager::getInstance()->create($classItem, ['product' => $product]);

            $this->data[] = $item;
        }
    }

    /**
     * Get module name from fixture
     *
     * @param FixtureInterface $product
     * @return string
     */
    protected function getModuleName(FixtureInterface $product)
    {
        preg_match('/^Magento\\\\([^\\\\]+)\\\\Test/', get_class($product), $match);
        return isset($match[1]) ? $match[1] : '';
    }

    /**
     * Persist fixture
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
     * @param string $key [optional]
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
     * Get source products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
