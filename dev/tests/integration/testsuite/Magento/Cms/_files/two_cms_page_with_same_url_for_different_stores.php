<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$store = $storeRepository->get('fixture_second_store');
/** @var $page \Magento\Cms\Model\Page */
$page = $objectManager->create(\Magento\Cms\Model\Page::class);
$page->setTitle('First test page')
    ->setIdentifier('page1')
    ->setStores([1])
    ->setIsActive(1)
    ->setPageLayout('1column')
    ->save();

/** @var $page \Magento\Cms\Model\Page */
$page = $objectManager->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Second test page')
    ->setIdentifier('page1')
    ->setStores([$store->getId()])
    ->setIsActive(1)
    ->setPageLayout('1column')
    ->save();
