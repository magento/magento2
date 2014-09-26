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

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductVirtual
 * Data for creation Catalog Product Virtual
 */
class CatalogProductVirtual extends AbstractRepository
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
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'is_virtual' => 'Yes',
            'url_key' => 'virtual-product%isolation%',
            'visibility' => 'Catalog, Search',
            'attribute_set_id' => ['dataSet' => 'default'],
            'name' => 'Virtual product %isolation%',
            'sku' => 'sku_virtual_product_%isolation%',
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 10.00, 'preset' => '-'],
            'checkout_data' => ['preset' => 'order_default'],
        ];

        $this->_data['50_dollar_product'] = [
            'name' => '50_dollar_product %isolation%',
            'sku' => '50_dollar_product_%isolation%',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'status' => 'Product online',
            'website_ids' => ['Main Website'],
            'is_virtual' => 'Yes',
            'url_key' => 'virtual-product%isolation%',
            'visibility' => 'Catalog, Search',
            'attribute_set_id' => ['dataSet' => 'default'],
            'quantity_and_stock_status' => [
                'qty' => 111.0000,
                'is_in_stock' => 'In Stock',
            ],
            'price' => ['value' => 50.00, 'preset' => '-'],
            'checkout_data' => ['preset' => '50_dollar_product']
        ];
    }
}
