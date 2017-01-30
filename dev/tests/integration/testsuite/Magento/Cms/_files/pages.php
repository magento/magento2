<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $page \Magento\Cms\Model\Page */
$page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Cms\Model\Page');
$page->setTitle('Cms Page 100')
    ->setIdentifier('page100')
    ->setStores([0])
    ->setIsActive(1)
    ->setContent('<h1>Cms Page 100 Title</h1>')
    ->setPageLayout('1column')
    ->save();

$page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Cms\Model\Page');
$page->setTitle('Cms Page Design Blank')
    ->setIdentifier('page_design_blank')
    ->setStores([0])
    ->setIsActive(1)
    ->setContent('<h1>Cms Page Design Blank Title</h1>')
    ->setPageLayout('1column')
    ->setCustomTheme('Magento/blank')
    ->save();
