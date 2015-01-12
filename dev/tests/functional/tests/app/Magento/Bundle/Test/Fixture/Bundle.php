<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Fixture;

use Magento\Catalog\Test\Fixture\Product;
use Mtf\Factory\Factory;
use Mtf\System\Config;

/**
 * Class Bundle
 */
class Bundle extends Product
{
    const GROUP = 'bundle';

    /**
     * List of fixtures from created products
     *
     * @var array
     */
    protected $products = [];

    /**
     * Custom constructor to create bundle product with assigned simple products
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = [])
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['item1_simple1::getName'] = [$this, 'productProvider'];
        $this->_placeholders['item1_simple1::getProductId'] = [$this, 'productProvider'];
        $this->_placeholders['item1_virtual2::getName'] = [$this, 'productProvider'];
        $this->_placeholders['item1_virtual2::getProductId'] = [$this, 'productProvider'];
    }

    /**
     * @param string $productData
     * @return string
     */
    protected function formatProductType($productData)
    {
        list(, $productData) = explode('_', $productData);
        return preg_replace('/\d/', '', $productData);
    }

    /**
     * Create bundle product
     *
     * @return $this|void
     */
    public function persist()
    {
        Factory::getApp()->magentoBundleCreateBundle($this);

        return $this;
    }

    /**
     * Get bundle options data to add product to shopping cart
     */
    public function getBundleOptions()
    {
        $options = [];
        $bundleOptions = $this->getData('fields/bundle_selections/value');
        foreach ($bundleOptions['bundle_options'] as $optionData) {
            $option = [
                'title' => $optionData['title'],
                'type' => $optionData['type'],
                'options' => [],
            ];

            foreach ($optionData['assigned_products'] as $productData) {
                $option['options'][] = ['title' => $productData['search_data']['name']];
            }
            $options[] = $option;
        }

        return $options;
    }

    /**
     * Get prices for verification
     *
     * @return array|string
     */
    public function getProductPrice()
    {
        $prices = $this->getData('checkout/prices');
        return $prices ?: parent::getProductPrice();
    }

    /**
     * Get options type, value and qty to select for adding to shopping cart
     *
     * @return array
     */
    public function getSelectionData()
    {
        $options = $this->getData('checkout/selection');
        $selectionData = [];
        foreach ($options as $option => $selection) {
            $fieldPrefix = 'fields/bundle_selections/value/bundle_options/';
            $selectionItem['type'] = $this->getData($fieldPrefix . $option . '/type');
            $selectionItem['title'] = $this->getData($fieldPrefix . $option . '/title');
            $selectionItem['value']['qty'] = $this->getData(
                $fieldPrefix . $option . '/assigned_products/' . $selection . '/data/selection_qty'
            );
            $selectionItem['value']['name'] = $this->getData(
                $fieldPrefix . $option . '/assigned_products/' . $selection . '/search_data/name'
            );
            $selectionData[] = $selectionItem;
        }

        return $selectionData;
    }

    /**
     * Initialize fixture data
     */
    protected function _initData()
    {
        parent::_initData();
        $this->_dataConfig = [
            'type_id' => 'bundle',
            'constraint' => 'Success',
            'create_url_params' => [
                'type' => 'bundle',
                'set' => static::DEFAULT_ATTRIBUTE_SET_ID,
            ],
            'input_prefix' => 'product',
        ];

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoBundleBundle($this->_dataConfig, $this->_data);
    }
}
