<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$resourceModel = $objectManager->create(Tablerate::class);
$resourceModel->getConnection()->delete($resourceModel->getMainTable());
