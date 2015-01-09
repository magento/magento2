<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class DownloadableProductInjectable
 * Data for creation Catalog Product Downloadable
 */
class DownloadableProductInjectable extends AbstractRepository
{
    /**
     * Constructor
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'name' => 'Test downloadable product %isolation%',
            'sku' => 'sku_test_downloadable_product_%isolation%',
            'price' => ['value' => 280.00, 'preset' => '-'],
            'type_id' => 'downloadable',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 90.0000,
                'is_in_stock' => 'In Stock',
            ],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'url_key' => 'test-downloadable-product-%isolation%',
            'is_virtual' => 'Yes',
            'downloadable_links' => ['preset' => 'default'],
            'website_ids' => ['Main Website'],
            'checkout_data' => ['preset' => 'default'],
        ];
        $this->_data['with_two_separately_links'] = [
            'name' => 'Downloadable product %isolation%',
            'sku' => 'downloadable_product_%isolation%',
            'url_key' => 'downloadable-product-%isolation%',
            'price' => ['value' => '20'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 1111,
                'is_in_stock' => 'In Stock',
            ],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'is_virtual' => 'Yes',
            'downloadable_links' => ['preset' => 'with_two_separately_links'],
            'checkout_data' => ['preset' => 'with_two_separately_links'],
        ];
        $this->_data['with_two_bought_links'] = [
            'name' => 'Downloadable product %isolation%',
            'sku' => 'downloadable_product_%isolation%',
            'url_key' => 'downloadable-product-%isolation%',
            'price' => ['value' => '20'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 1111,
                'is_in_stock' => 'In Stock',
            ],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'is_virtual' => 'Yes',
            'downloadable_links' => ['preset' => 'with_two_separately_links'],
            'checkout_data' => ['preset' => 'with_two_bought_links'],
        ];
    }
}
