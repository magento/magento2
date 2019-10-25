<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Store\Model\Store::class);
$store->load('default', 'code');

$page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms page default store')
    ->setIdentifier('page_default_store')
    ->setStores([$store->getId()])
    ->setIsActive(1)
    ->setContent('<h1>Cms page default store</h1>')
    ->setPageLayout('1column');
$page->save();
