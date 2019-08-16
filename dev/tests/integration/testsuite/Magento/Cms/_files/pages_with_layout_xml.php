<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$pageFactory = $objectManager->get(PageModelFactory::class);
/** @var PageModel $page */
$page = $pageFactory->create();
$page->setIdentifier('test_custom_layout_page_1');
$page->setTitle('Test Page');
$page->setCustomLayoutUpdateXml('tst');
$page->save();
/** @var PageModel $page2 */
$page2 = $pageFactory->create();
$page2->setIdentifier('test_custom_layout_page_2');
$page2->setTitle('Test Page 2');
$page2->save();
