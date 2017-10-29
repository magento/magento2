<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

for ($i = 0; $i < 3; $i++) {
    /** @var $website \Magento\Store\Model\Website */
    $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
    $website->setData([
        'code' => 'test_' . $i,
        'name' => 'Test Website ' . $i,
        'default_group_id' => '1',
        'is_default' => '0',
    ]);
    $website->save();
}

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/* Refresh stores memory cache */
$objectManager->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
