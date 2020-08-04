<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Observer\SwitchPriceAttributeScopeOnConfigChange;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Event\Observer;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Config $configResource */
$configResource = $objectManager->get(Config::class);
$configResource->deleteConfig(Data::XML_PATH_PRICE_SCOPE, 'default', 0);
$observer = $objectManager->get(Observer::class);
$objectManager->get(SwitchPriceAttributeScopeOnConfigChange::class)->execute($observer);

require __DIR__ . '/dynamic_bundle_product_with_special_price_rollback.php';
require __DIR__ . '/../../Store/_files/second_website_with_store_group_and_store_rollback.php';
