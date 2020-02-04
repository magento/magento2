<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\ResourceConnection;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');

/** @var ResourceConnection $resource */
$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();
$resourceModel = $objectManager->create(Tablerate::class);
$entityTable = $resourceModel->getTable('shipping_tablerate');
$data =
    [
        'website_id' => $website->getId(),
        'dest_country_id' => 'US',
        'dest_region_id' => 0,
        'dest_zip' => '*',
        'condition_name' => 'package_qty',
        'condition_value' => 1,
        'price' => 20,
        'cost' => 20
    ];
$connection->query(
    "INSERT INTO {$entityTable} (`website_id`,  `dest_country_id`, `dest_region_id`, `dest_zip`, `condition_name`,"
    . "`condition_value`, `price`, `cost`) VALUES (:website_id,  :dest_country_id, :dest_region_id, :dest_zip,"
    . " :condition_name, :condition_value, :price, :cost);",
    $data
);
