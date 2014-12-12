<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $this \Magento\Setup\Module\SetupModule */
$connection = $this->getConnection();
$connection->dropTable('core_cache_option');
