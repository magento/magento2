<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$queries = [
    [
        'text' => '1st query',
        'results' => 1,
        'popularity' => 5,
        'display' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => '2nd query',
        'results' => 1,
        'popularity' => 10,
        'display' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => '3rd query',
        'results' => 1,
        'popularity' => 1,
        'display' => 1,
        'active' => 1,
        'processed' => 1
    ],
    [
        'text' => '4th query',
        'results' => 0,
        'popularity' => 1,
        'display' => 1,
        'active' => 1,
        'processed' => 1
    ],
];

foreach ($queries as $queryData) {
    /** @var $queryData \Magento\Search\Model\Query */
    $query = $objectManager->create(\Magento\Search\Model\Query::class);
    $query->setStoreId(1);
    $query->setQueryText(
        $queryData['text']
    )->setNumResults(
        $queryData['results']
    )->setPopularity(
        $queryData['popularity']
    )->setDisplayInTerms(
        $queryData['display']
    )->setIsActive(
        $queryData['active']
    )->setIsProcessed(
        $queryData['processed']
    );
    $query->save();
}
