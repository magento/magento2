<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$data = [
    [
        'title' => 'simplePage',
        'is_active' => 1,
    ],
    [
        'title' => 'simplePage01',
        'is_active' => 1,
    ],
    [
        'title' => '01simplePage',
        'is_active' => 1,
    ],
    [
        'title' => 'Page with 1column layout',
        'is_active' => 1,
        'content' => '<h1>Test Page Content</h1>',
        'page_layout' => '1column',
    ],
    [
        'title' => 'Page with unavailable layout',
        'content' => '<h1>Test Page Content</h1>',
        'is_active' => 1,
        'page_layout' => 'unavailable-layout',
    ],
];

/** @var PageRepositoryInterface $pageRepository */
$pageRepository = $objectManager->get(PageRepositoryInterface::class);
foreach ($data as $item) {
    $page = $objectManager->create(PageInterface::class, ['data' => $item]);
    $pageRepository->save($page);
}
