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
$page->load('test_custom_layout_page_1', PageModel::IDENTIFIER);
if ($page->getId()) {
    $page->delete();
}
/** @var PageModel $page2 */
$page2 = $pageFactory->create();
$page2->load('test_custom_layout_page_2', PageModel::IDENTIFIER);
if ($page2->getId()) {
    $page2->delete();
}
/** @var PageModel $page3 */
$page3 = $pageFactory->create();
$page3->load('test_custom_layout_page_3', PageModel::IDENTIFIER);
if ($page3->getId()) {
    $page3->delete();
}
