<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $database \Magento\MediaStorage\Helper\File\Storage\Database */
$database = $objectManager->get(\Magento\MediaStorage\Helper\File\Storage\Database::class);

$database->getStorageDatabaseModel()->init();

$config = $objectManager->get(Magento\Framework\App\Config\ConfigResource\ConfigInterface::class);
$config->saveConfig('system/media_storage_configuration/media_storage', '1');
$config->saveConfig('system/media_storage_configuration/media_database', 'default_setup');
$objectManager->get(Magento\Framework\App\Config\ReinitableConfigInterface::class)->reinit();
