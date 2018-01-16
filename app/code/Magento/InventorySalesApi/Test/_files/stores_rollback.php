<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$storeCodes = ['eu_store', 'us_store', 'global_store'];

foreach ($storeCodes as $storeCode) {
    /** @var store $store */
    $store = Bootstrap::getObjectManager()->create(Store::class);
    $store->load($storeCode);
    $store->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
