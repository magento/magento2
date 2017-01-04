<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Fixture\Cart;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Items extends DataSource
{
    /**
     * List fixture products.
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
    }

    /**
     * Get module name from fixture.
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
     * Return prepared dataset.
     *
     * @param null|string $key
     * @return array
     */
    public function getData($key = null)
    {
        foreach ($this->products as $product) {
            $classItem = 'Magento\\' . $this->getModuleName($product) . '\Test\Fixture\Cart\Item';
            $item = ObjectManager::getInstance()->create($classItem, ['product' => $product]);
            $item->getData();
            $this->data[] = $item;
        }

        return $this->data;
    }

    /**
     * Get source products.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
