<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Framework\App\ResourceConnection $resource */
$resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Framework\App\ResourceConnection::class);

$connection = $resource->getConnection('default');
$connection->truncateTable($resource->getTableName('search_synonyms'));
