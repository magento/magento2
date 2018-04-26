<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $query \Magento\Search\Model\Query */
$query = $objectManager->get(\Magento\Search\Model\Query::class);

$queries = [
    '1st query',
    '2nd query',
    '3rd query',
    '4th query',
];

foreach ($queries as $queryText) {
    try {
        $query->loadByQueryText($queryText);
        $query->delete();
    } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    }
}
