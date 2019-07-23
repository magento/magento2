<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Config $config */
$config = $objectManager->create(Config::class);
$config->deleteConfig(State::INDEXER_ENABLED_XML_PATH);
$objectManager->get(ReinitableConfigInterface::class)->reinit();
