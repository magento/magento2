<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Store $store */
$store = $objectManager->get(Store::class);
$store->load('test', 'code');
if ($store->getId()) {
    $store->delete();
}

/** @var Store $store */
$store = $objectManager->get(Store::class);
$store->load('test', 'code');
if ($store->getId()) {
    $store->delete();
}

/** @var UrlRewriteCollectionFactory $urlRewriteCollectionFactory */
$urlRewriteCollectionFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    UrlRewriteCollectionFactory::class
);
/** @var UrlRewriteCollection $urlRewriteCollection */
$urlRewriteCollection = $urlRewriteCollectionFactory->create();
$urlRewriteCollection
    ->addFieldToFilter('store_id', ['nin' => [0, 1]]);
$urlRewrites = $urlRewriteCollection->getItems();
/** @var UrlRewrite $urlRewrite */
foreach ($urlRewrites as $urlRewrite) {
    try {
        $urlRewrite->delete();
    } catch (\Exception $exception) {
        // already removed
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
