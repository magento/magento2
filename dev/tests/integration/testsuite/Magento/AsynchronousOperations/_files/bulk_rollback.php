<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @var $resource Magento\Framework\App\ResourceConnection
 */
$resource = Bootstrap::getObjectManager()->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();
$bulkTable = $resource->getTableName('magento_bulk');

$connection->query("DELETE FROM {$bulkTable};");
