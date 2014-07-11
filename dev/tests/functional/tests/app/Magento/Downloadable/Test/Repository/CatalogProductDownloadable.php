<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductDownloadable
 * Data for creation Catalog Product Downloadable
 */
class CatalogProductDownloadable extends AbstractRepository
{
    /**
     * @constructor
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'name' => 'Test downloadable product %isolation%',
            'sku' => 'sku_test_downloadable_product_%isolation%',
            'price' => 280.00,
            'type_id' => 'downloadable',
            'tax_class' => ['Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 90.0000,
                'is_in_stock' => 'In Stock',
            ],
            'status' => 'Product online',
            'category_ids' => ['presets' => 'default_subcategory'],
            'visibility' => 'Catalog, Search',
            'url_key' => 'test-downloadable-product-%isolation%',
            'is_virtual' => 'Yes',
            'links_title' => 'Links',
            'links_purchased_separately' => 'Yes',
            'downloadable' => [
                'link' => [
                    [
                        'title' => 'Link title',
                        'price' => '1',
                        'number_of_downloads' => '1',
                        'is_shareable' => 'Use config',
                        'sample' => [
                            'type' => 'url',
                            'url' => 'http://example.com/',
                        ],
                        'type' => 'url',
                        'link_url' => 'http://example.com/',
                    ]
                ]
            ],
            'website_ids' => ['Main Website'],
        ];
    }
}
