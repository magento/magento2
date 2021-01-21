<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$pageFactory = $objectManager->get(PageModelFactory::class);

/**
 * @var PageModel $page
 * @var PageResource $pageResource
 */
$page = $pageFactory->create();
$pageResource = $objectManager->create(PageResource::class);
$pageResource->load($page, 'test_custom_layout_page_1', PageModel::IDENTIFIER);
if ($page->getId()) {
    $pageResource->delete($page);
}

/** @var PageModel $page2 */
$page2 = $pageFactory->create();
$pageResource->load($page2, 'test_custom_layout_page_2', PageModel::IDENTIFIER);
if ($page2->getId()) {
    $pageResource->delete($page2);
}

/** @var PageModel $page3 */
$page3 = $pageFactory->create();
$pageResource->load($page3, 'test_custom_layout_page_3', PageModel::IDENTIFIER);
if ($page3->getId()) {
    $pageResource->delete($page3);
}
