<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Framework\App\Config\ConfigResource\ConfigInterface $config */
$config = $objectManager->get(Magento\Framework\App\Config\ConfigResource\ConfigInterface::class);
$config->deleteConfig('system/media_storage_configuration/media_storage');
$config->deleteConfig('system/media_storage_configuration/media_database');
$objectManager->get(Magento\Framework\App\Config\ReinitableConfigInterface::class)->reinit();
