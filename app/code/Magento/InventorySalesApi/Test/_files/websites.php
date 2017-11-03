<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

for ($i = 0; $i < 3; $i++) {
    /** @var $website Website */
    $website = Bootstrap::getObjectManager()->create(Website::class);
    $website->setData([
        'code' => 'test_' . $i,
        'name' => 'Test Website ' . $i,
        'default_group_id' => '1',
        'is_default' => '0',
    ]);
    $website->save();
}

$objectManager = Bootstrap::getObjectManager();
/* Refresh stores memory cache */
$objectManager->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
