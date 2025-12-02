<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
