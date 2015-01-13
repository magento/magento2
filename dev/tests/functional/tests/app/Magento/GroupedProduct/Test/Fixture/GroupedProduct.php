<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Fixture;

use Magento\Catalog\Test\Fixture\Product;
use Mtf\Factory\Factory;
use Mtf\System\Config;

/**
 * Class GroupedProduct
 * Grouped product data
 *
 */
class GroupedProduct extends Product
{
    const GROUP = 'grouped-product';

    /**
     * List of fixtures from created products
     *
     * @var array
     */
    protected $products = [];

    /**
     * Custom constructor to create Grouped product
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = [])
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['simple::getName'] = [$this, 'productProvider'];
        $this->_placeholders['virtual::getName'] = [$this, 'productProvider'];
        $this->_placeholders['downloadable::getName'] = [$this, 'productProvider'];
        $this->_placeholders['simple::getProductId'] = [$this, 'productProvider'];
        $this->_placeholders['virtual::getProductId'] = [$this, 'productProvider'];
        $this->_placeholders['downloadable::getProductId'] = [$this, 'productProvider'];
    }

    /**
     * Get Associated Products
     *
     * @return array
     */
    public function getAssociatedProducts()
    {
        return $this->products;
    }

    /**
     * Get Associated Product Names
     *
     * @return array
     */
    public function getAssociatedProductNames()
    {
        $names = [];
        foreach ($this->getData('fields/grouped_products/value') as $product) {
            $names[] = $product['search_data']['name'];
        }
        return $names;
    }

    /**
     * Init Data
     */
    protected function _initData()
    {
        $this->_dataConfig = [
            'type_id' => 'grouped',
            'constraint' => 'Success',
            'create_url_params' => [
                'type' => 'grouped',
                'set' => static::DEFAULT_ATTRIBUTE_SET_ID,
            ],
        ];
        $this->_data = [
            'fields' => [
                'name' => [
                    'value' => 'Grouped Product %isolation%',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                ],
                'sku' => [
                    'value' => 'grouped_sku_%isolation%',
                    'group' => static::GROUP_PRODUCT_DETAILS,
                ],
                'grouped_products' => [
                    'value' => [
                        'assigned_product_0' => [
                            'search_data' => [
                                'name' => '%simple::getName%',
                            ],
                            'data' => [
                                'selection_qty' => [
                                    'value' => 1,
                                ],
                                'product_id' => [
                                    'value' => '%simple::getProductId%',
                                ],
                            ],
                        ],
                        'assigned_product_1' => [
                            'search_data' => [
                                'name' => '%virtual::getName%',
                            ],
                            'data' => [
                                'selection_qty' => [
                                    'value' => 1,
                                ],
                                'product_id' => [
                                    'value' => '%virtual::getProductId%',
                                ],
                            ],
                        ],
                        'assigned_product_2' => [
                            'search_data' => [
                                'name' => '%downloadable::getName%',
                            ],
                            'data' => [
                                'selection_qty' => [
                                    'value' => 1,
                                ],
                                'product_id' => [
                                    'value' => '%downloadable::getProductId%',
                                ],
                            ],
                        ],
                    ],
                    'group' => static::GROUP,
                ],
            ],
        ];

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoGroupedProductGroupedProduct($this->_dataConfig, $this->_data);
    }
}
