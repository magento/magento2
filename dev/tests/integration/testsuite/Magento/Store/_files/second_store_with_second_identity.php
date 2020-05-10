<?php
/**
 * Create fixture store with second identity
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require_once __DIR__ . '/second_store.php';

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$store = $objectManager->create(Store::class);
if ($storeId = $store->load('fixture_second_store', 'code')->getId()) {
    /** @var Config $configResource */
    $configResource = $objectManager->get(Config::class);
    $configResource->saveConfig(
        'trans_email/ident_general/name',
        'Fixture Store Owner',
        ScopeInterface::SCOPE_STORES,
        $storeId

    );
    $configResource->saveConfig(
        'trans_email/ident_general/email',
        'fixture.store.owner@example.com',
        ScopeInterface::SCOPE_STORES,
        $storeId
    );
    $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
    $scopeConfig->clean();
}
