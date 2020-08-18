<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $page \Magento\Cms\Model\Page */
$page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$page->setTitle('Cms Page 100')
    ->setIdentifier('fixture_page_with_asset')
    ->setStores([0])
    ->setIsActive(1)
    ->setContent('content {{media url="testDirectory/path.jpg"}} content')
    ->setContentHeading('<h2>Cms Page 100 Title</h2>')
    ->setMetaTitle('Cms Meta title for page100')
    ->setMetaKeywords('Cms Meta Keywords for page100')
    ->setMetaDescription('Cms Meta Description for page100')
    ->setPageLayout('1column')
    ->save();
