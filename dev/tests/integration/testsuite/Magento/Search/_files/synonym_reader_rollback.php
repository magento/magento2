<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\App\ResourceConnection $resource */
$resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('\Magento\Framework\App\ResourceConnection');

$connection = $resource->getConnection('default');
$connection->truncateTable($resource->getTableName('search_synonyms'));
