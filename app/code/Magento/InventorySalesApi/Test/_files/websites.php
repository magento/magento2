<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

$websiteCodes = ['eu_website', 'us_website', 'global_website'];

foreach ($websiteCodes as $websiteCode) {
    /** @var Website $website */
    $website = Bootstrap::getObjectManager()->create(Website::class);
    $website->setData([
        'code' => $websiteCode,
        'name' => 'Test Website ' . $websiteCode,
        'default_group_id' => '1',
        'is_default' => '0',
    ]);
    $website->save();
}

$objectManager = Bootstrap::getObjectManager();
/* Refresh stores memory cache */
$objectManager->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
