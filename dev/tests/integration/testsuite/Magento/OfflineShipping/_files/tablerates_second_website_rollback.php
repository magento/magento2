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
$connection->query("DELETE FROM {$entityTable};");
