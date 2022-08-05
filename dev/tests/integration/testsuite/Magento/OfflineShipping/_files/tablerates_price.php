<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/**
 * @var Tablerate $resourceModel
 */
$resourceModel = $objectManager->create(Tablerate::class);
$tableRatesData = [
        [
            'website_id' => 1,
            'dest_country_id' => 'US',
            'dest_region_id' => 0,
            'dest_zip' => '*',
            'condition_name' => 'package_value_with_discount',
            'condition_value' => 0.00,
            'price' => 15,
            'cost' => 0
        ],
        [
            'website_id' => 1,
            'dest_country_id' => 'US',
            'dest_region_id' => 0,
            'dest_zip' => '*',
            'condition_name' => 'package_value_with_discount',
            'condition_value' => 50.00,
            'price' => 10,
            'cost' => 0
        ],
        [
            'website_id' => 1,
            'dest_country_id' => 'US',
            'dest_region_id' => 0,
            'dest_zip' => '*',
            'condition_name' => 'package_value_with_discount',
            'condition_value' => 100.00,
            'price' => 5,
            'cost' => 0
        ]
    ];
$columns = [
    'website_id',
    'dest_country_id',
    'dest_region_id',
    'dest_zip',
    'condition_name',
    'condition_value',
    'price',
    'cost'
];
$resourceModel->getConnection()->insertArray($resourceModel->getMainTable(), $columns, $tableRatesData);
