<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();
$resourceModel = $objectManager->create(\Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate::class);
$entityTable = $resourceModel->getTable('shipping_tablerate');
$data =
    [
        'website_id' => 1,
        'dest_country_id' => 'US',
        'dest_region_id' => 0,
        'dest_zip' => '*',
        'condition_name' => 'package_weight',
        'condition_value' => 0,
        'price' => 10,
        'cost' => 0
    ];
$connection->query(
    "INSERT INTO {$entityTable} (`website_id`,  `dest_country_id`, `dest_region_id`, `dest_zip`, `condition_name`,"
    . "`condition_value`, `price`, `cost`) VALUES (:website_id,  :dest_country_id, :dest_region_id, :dest_zip,"
    . " :condition_name, :condition_value, :price, :cost);",
    $data
);
