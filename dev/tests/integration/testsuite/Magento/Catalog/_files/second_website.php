<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$website = $objectManager->get(Magento\Store\Model\Website::class);
$website->load('test_website', 'code');

if (!$website->getId()) {
    /** @var Magento\Store\Model\Website $website */
    $website->setData(
        [
            'code' => 'test_website',
            'name' => 'Test Website',
        ]
    );

    $website->save();
}

$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
