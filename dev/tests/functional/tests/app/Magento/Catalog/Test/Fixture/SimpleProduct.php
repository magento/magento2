<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\System\Config;

/**
 * Class SimpleProduct
 * Fixture simple product
 */
class SimpleProduct extends Product
{
    const PRICE_VALUE = 'price.value';

    /**
     * Custom constructor to create product with assigned category
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = [])
    {
        $this->_placeholders[self::PRICE_VALUE] = 10;

        parent::__construct($configuration, $placeholders);
    }

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        parent::_initData();
        $this->_dataConfig = [
            'constraint' => 'Success',
            'grid_filter' => ['name'],
            'create_url_params' => ['type' => 'simple', 'set' => static::DEFAULT_ATTRIBUTE_SET_ID],
            'input_prefix' => 'product',
        ];

        $data = $this->_getPreparedData();
        $this->_data['fields'] = array_merge($this->_data['fields'], $data);

        $this->_repository = Factory::getRepositoryFactory()->getMagentoCatalogSimpleProduct(
            $this->_dataConfig,
            $this->_data
        );
    }

    /**
     * Get data for the product
     *
     * @return array
     */
    protected function _getPreparedData()
    {
        return [
            'price' => [
                'value' => '%' . self::PRICE_VALUE . '%',
                'group' => static::GROUP_PRODUCT_DETAILS,
            ],
            'tax_class_id' => [
                'value' => 'Taxable Goods',
                'input_value' => '2',
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input' => 'select',
            ],
            'qty' => [
                'value' => 1000,
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input_name' => 'product[quantity_and_stock_status][qty]',
            ],
            'quantity_and_stock_status' => [
                'value' => 'In Stock',
                'input_value' => 1,
                'group' => static::GROUP_PRODUCT_DETAILS,
                'input_name' => 'product[quantity_and_stock_status][is_in_stock]',
            ],
            'weight' => ['value' => '1', 'group' => static::GROUP_PRODUCT_DETAILS],
            'product_website_1' => [
                'value' => 'Yes',
                'input_value' => 1,
                'group' => static::GROUP_PRODUCT_WEBSITE,
                'input' => 'checkbox',
                'input_name' => 'product[website_ids][]',
            ],
            'inventory_manage_stock' => [
                'value' => 'No',
                'input_value' => '0',
                'group' => static::GROUP_PRODUCT_INVENTORY,
                'input' => 'select',
                'input_name' => 'product[stock_data][manage_stock]',
            ],
        ];
    }
}
