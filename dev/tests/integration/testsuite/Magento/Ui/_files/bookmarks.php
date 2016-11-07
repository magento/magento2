<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Model\Bookmark;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$bookmarks = [
    [
        'user_id' => 1,
        'namespace' => 'bm_namespace',
        'identifier' => 'first',
        'current' => 1,
        'config' => '{}',
        'title' => 'Bb'
    ],
    [
        'user_id' => 1,
        'namespace' => 'bm_namespace',
        'identifier' => 'second',
        'current' => 0,
        'config' => '{1}',
        'title' => 'Aa'
    ],
    [
        'user_id' => 1,
        'namespace' => 'new_namespace',
        'identifier' => 'third',
        'current' => 1,
        'config' => '{}',
        'title' => 'Default View'
    ],
];

foreach ($bookmarks as $bookmarkData) {
    /** @var Bookmark $bookmark */
    $bookmark = $objectManager->create(BookmarkInterface::class);
    $bookmark
        ->setData($bookmarkData)
        ->save();
}
