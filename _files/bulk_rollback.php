<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @var $resource Magento\Framework\App\ResourceConnection
 */
$resource = Bootstrap::getObjectManager()->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();
$bulkTable = $resource->getTableName('magento_bulk');

$connection->query("DELETE FROM {$bulkTable};");
