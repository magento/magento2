<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types = 1);

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ConfigInterface $config */
$config = $objectManager->get(ConfigInterface::class);
$config->deleteConfig('general/country/allow');
$objectManager->get(ReinitableConfigInterface::class)->reinit();
