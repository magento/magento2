<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$store = $objectManager->create(\Magento\Store\Model\Store::class);
$storeId = $store->load('fixture_second_store', 'code')->getId();

if ($storeId) {
    $configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
    $configResource->deleteConfig(
        'trans_email/ident_general/name',
        ScopeInterface::SCOPE_STORES,
        $storeId
    );
    $configResource->deleteConfig(
        'trans_email/ident_general/email',
        ScopeInterface::SCOPE_STORES,
        $storeId
    );
}

require_once __DIR__ . '/second_store_rollback.php';
