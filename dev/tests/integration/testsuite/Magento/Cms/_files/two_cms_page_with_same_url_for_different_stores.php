<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store.php');

$objectManager = Bootstrap::getObjectManager();

/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$store = $storeRepository->get('fixture_second_store');

/** @var PageRepositoryInterface $pageRepository */
$pageRepository = $objectManager->create(PageRepositoryInterface::class);

/** @var $page Page */
$page = $objectManager->create(Page::class);
$page->setTitle('First test page')
    ->setIdentifier('page1')
    ->setStores([1])
    ->setIsActive(1)
    ->setPageLayout('1column');
$pageRepository->save($page);

/** @var $page Page */
$page = $objectManager->create(Page::class);
$page->setTitle('Second test page')
    ->setIdentifier('page1')
    ->setStores([$store->getId()])
    ->setIsActive(1)
    ->setPageLayout('1column');
$pageRepository->save($page);
