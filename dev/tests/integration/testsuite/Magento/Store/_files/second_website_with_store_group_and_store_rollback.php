<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var WebsiteInterface $website */
$website = $objectManager->create(WebsiteInterfaceFactory::class)->create();
$websiteId = $website->load('test', 'code')->getId();
if ($website->load('test', 'code')->getId()) {
    $website->delete();
}
/** @var StoreInterface $store */
$store = $objectManager->create(StoreInterfaceFactory::class)->create();
if ($store->load('fixture_second_store', 'code')->getId()) {
    $store->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
