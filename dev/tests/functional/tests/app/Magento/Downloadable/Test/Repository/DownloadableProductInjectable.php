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
            'checkout_data' => ['preset' => 'default']
        ];
        $this->_data['with_two_separately_links'] = [
            'name' => 'Downloadable product %isolation%',
            'sku' => 'downloadable_product_%isolation%',
            'url_key' => 'downloadable-product-%isolation%',
            'price' => ['value' => '20'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 1111,
                'is_in_stock' => 'In Stock'
            ],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'is_virtual' => 'Yes',
            'downloadable_links' => ['preset' => 'with_two_separately_links'],
            'checkout_data' => ['preset' => 'with_two_separately_links']
        ];
    }
}
