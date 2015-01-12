<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Crosssell;
use Mtf\Factory\Factory;

class CrosssellProducts extends AssignProducts
{
    /**
     * @var string
     */
    protected $assignType = 'crosssell';

    /**
     * @var string
     */
    protected $group = 'crosssells';

    /**
     * @var array
     */
    protected $_products = [];

    /**
     * Init Data
     */
    protected function _initData()
    {
        $this->_dataConfig = [
            'assignType ' => $this->assignType,
        ];
        $productsArray = [];
        foreach ($this->_products as $key => $product) {
            /** @var $product \Magento\Catalog\Test\Fixture\Product */
            $productsArray['product_' . $key] = [
                'sku' => $product->getSku(),
                'name' => $product->getName(),
            ];
        }
        $this->_data['fields']['cross_sell_products']['value'] = $productsArray;
        $this->_data['fields']['cross_sell_products']['group'] = $this->group;

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogAssignProducts($this->_dataConfig, $this->_data);
    }

    /**
     * Set specified product to local data
     *
     * @param array $products
     * @return $this
     */
    public function setProducts(array $products)
    {
        $this->_products = $products;
        $this->_initData();
        return $this;
    }
}
