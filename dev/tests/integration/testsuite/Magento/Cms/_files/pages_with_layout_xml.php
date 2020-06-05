<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Cms\Model\Page as PageModel;
use Magento\Cms\Model\PageFactory as PageModelFactory;
use Magento\TestFramework\Cms\Model\CustomLayoutManager;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$objectManager->configure([
    'preferences' => [
        \Magento\Cms\Model\Page\CustomLayoutManagerInterface::class =>
            \Magento\TestFramework\Cms\Model\CustomLayoutManager::class
    ]
]);
$pageFactory = $objectManager->get(PageModelFactory::class);
/** @var CustomLayoutManager $fakeManager */
$fakeManager = $objectManager->get(CustomLayoutManager::class);
$layoutRepo = $objectManager->create(PageModel\CustomLayoutRepositoryInterface::class, ['manager' => $fakeManager]);

/** @var PageModel $page */
$page = $pageFactory->create(['customLayoutRepository' => $layoutRepo]);
$page->setIdentifier('test_custom_layout_page_1');
$page->setTitle('Test Page');
$page->setCustomLayoutUpdateXml('<container />');
$page->setLayoutUpdateXml('<container />');
$page->setIsActive(true);
$page->setStoreId(0);
$page->save();
/** @var PageModel $page2 */
$page2 = $pageFactory->create(['customLayoutRepository' => $layoutRepo]);
$page2->setIdentifier('test_custom_layout_page_2');
$page2->setTitle('Test Page 2');
$page->setIsActive(true);
$page->setStoreId(0);
$page2->save();
/** @var PageModel $page3 */
$page3 = $pageFactory->create(['customLayoutRepository' => $layoutRepo]);
$page3->setIdentifier('test_custom_layout_page_3');
$page3->setTitle('Test Page 3');
$page3->setStores([0]);
$page3->setIsActive(1);
$page3->setContent('<h1>Test Page</h1>');
$page3->setPageLayout('1column');
$page3->save();
$fakeManager->fakeAvailableFiles((int)$page3->getId(), ['test_selected']);
$page3->setData('layout_update_selected', 'test_selected');
$page3->save();
