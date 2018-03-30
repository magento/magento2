<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\UrlRewrite\Model\OptionProvider;
use \Magento\UrlRewrite\Model\UrlRewrite;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/** Create fixture store */
require dirname(dirname(__DIR__)) . '/Store/_files/second_store.php';

/** @var UrlRewrite $rewrite */
/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $rewriteResource */
$rewriteResource = $objectManager->create(
    \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite::class
);
/** @var \Magento\Cms\Model\ResourceModel\Page $pageResource */
$pageResource = $objectManager->create(
    \Magento\Cms\Model\ResourceModel\Page::class
);
/** @var StoreManagerInterface $storeManager */
$storeManager = Bootstrap::getObjectManager()
    ->get(StoreManagerInterface::class);

/** @var Store $secondStore */
$secondStore = Bootstrap::getObjectManager()->create(Store::class);
$secondStore->load('fixture_second_store');
$secondStoreId = $secondStore->getId();
$storeID = 1;

/** @var $page \Magento\Cms\Model\Page */
$page = Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms Page A')
    ->setIdentifier('page-a')
    ->setIsActive(1)
    ->setContent('<h1>Cms Page A</h1>')
    ->setPageLayout('1column')
    ->setStores([$storeID, $secondStoreId]);
$pageResource->save($page);

$page = Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms B')
    ->setIdentifier('page-b')
    ->setIsActive(1)
    ->setContent('<h1>Cms Page B</h1>')
    ->setPageLayout('1column')
    ->setCustomTheme('Magento/blank')
    ->setStores([$storeID, $secondStoreId]);
$pageResource->save($page);

$page = Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms C')
    ->setIdentifier('page-c')
    ->setIsActive(1)
    ->setContent('<h1>Cms Page C</h1>')
    ->setPageLayout('1column')
    ->setCustomTheme('Magento/blank')
    ->setStores([$storeID, $secondStoreId]);
$pageResource->save($page);

$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('custom')
    ->setRequestPath('page-one/')
    ->setTargetPath('page-a/')
    ->setRedirectType(OptionProvider::PERMANENT)
    ->setStoreId($storeID)
    ->setDescription('From page-one/ to page-a/');
$rewriteResource->save($rewrite);

$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('custom')
    ->setRequestPath('page-two')
    ->setTargetPath('page-b')
    ->setRedirectType(OptionProvider::PERMANENT)
    ->setStoreId($storeID)
    ->setDescription('From page-two to page-b');
$rewriteResource->save($rewrite);

$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('custom')
    ->setRequestPath('page-similar')
    ->setTargetPath('page-a')
    ->setRedirectType(OptionProvider::PERMANENT)
    ->setStoreId($storeID)
    ->setDescription('From age-similar without trailing slash to page-a');
$rewriteResource->save($rewrite);

$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('custom')
    ->setRequestPath('page-similar/')
    ->setTargetPath('page-b')
    ->setRedirectType(OptionProvider::PERMANENT)
    ->setStoreId($storeID)
    ->setDescription('From age-similar with trailing slash to page-b');
$rewriteResource->save($rewrite);

//Emulating auto-generated aliases (like the ones used for categories).
//Rewrite rule for the 1st store.
$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('custom')
    ->setRequestPath('page-c-on-1st-store')
    ->setTargetPath('page-c')
    ->setRedirectType(0)
    ->setStoreId($storeID);
$rewriteResource->save($rewrite);
//Rewrite rule for the 2nd store.
$rewrite = $objectManager->create(UrlRewrite::class);
$rewrite->setEntityType('custom')
    ->setRequestPath('page-c-on-2nd-store')
    ->setTargetPath('page-c')
    ->setRedirectType(0)
    ->setStoreId($secondStoreId);
$rewriteResource->save($rewrite);
