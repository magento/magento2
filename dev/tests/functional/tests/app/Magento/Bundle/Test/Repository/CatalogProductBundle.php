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

namespace Magento\Bundle\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogProductBundle
 *
 */
class CatalogProductBundle extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['BundleDynamic_sku_1073507449'] = [
            'sku' => 'BundleDynamic_sku_10735074493',
            'name' => 'BundleDynamic 1073507449',
            'price' => [
                'price_from' => 1,
                'price_to' => 2
            ],
            'short_description' => '',
            'description' => '',
            'tax_class_id' => '2',
            'sku_type' => '0',
            'price_type' => '0',
            'weight_type' => '0',
            'status' => '1',
            'shipment_type' => '1',
            'mtf_dataset_name' => 'BundleDynamic_sku_1073507449'
        ];

        $this->_data['BundleDynamic_sku_215249172'] = [
            'sku' => 'BundleDynamic_sku_215249172',
            'name' => 'BundleDynamic 215249172',
            'price' => [
                'price_from' => 3,
                'price_to' => 4
            ],
            'short_description' => '',
            'description' => '',
            'tax_class_id' => '2',
            'sku_type' => '0',
            'weight_type' => '0',
            'price_type' => '0',
            'shipment_type' => '1',
            'mtf_dataset_name' => 'BundleDynamic_sku_215249172'
        ];
    }
}
