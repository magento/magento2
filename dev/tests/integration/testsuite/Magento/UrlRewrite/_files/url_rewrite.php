<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\UrlRewrite\Model\OptionProvider;
use \Magento\UrlRewrite\Model\UrlRewrite;

/** @var UrlRewrite $rewrite */
/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $rewriteResource */
$rewriteResource = $objectManager->create(\Magento\UrlRewrite\Model\ResourceModel\UrlRewrite::class);
/** @var \Magento\Cms\Model\ResourceModel\Page $pageResource */
$pageResource = $objectManager->create(\Magento\Cms\Model\ResourceModel\Page::class);

$storeID = 1;

/** @var $page \Magento\Cms\Model\Page */
$page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms Page A')
    ->setIdentifier('page-a')
    ->setStores([$storeID])
    ->setIsActive(1)
    ->setContent('<h1>Cms Page A</h1>')
    ->setPageLayout('1column');
$pageResource->save($page);

$page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms B')
    ->setIdentifier('page-b')
    ->setStores([$storeID])
    ->setIsActive(1)
    ->setContent('<h1>Cms Page B</h1>')
    ->setPageLayout('1column')
    ->setCustomTheme('Magento/blank');
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
