<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

$codes = ['eu', 'us', 'global'];

foreach ($codes as $code) {
    $storeCode = "{$code}_store";
    $websiteCode = "{$code}_website";
    $store = Bootstrap::getObjectManager()->create(Store::class);
    $website = Bootstrap::getObjectManager()->create(Website::class);
    $website->load($websiteCode);
    $store->setData(
        [
            'code' => $storeCode,
            'website_id' => $website->getId(),
            'group_id' => '1',
            'name' => $storeCode,
            'sort_order' => '0',
            'is_active' => '1',
        ]
    );
    $store->save();
}
Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
