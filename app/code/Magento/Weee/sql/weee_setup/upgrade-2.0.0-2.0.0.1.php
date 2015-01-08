<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$installer = $this;
/** @var $installer \Magento\Setup\Module\SetupModule */
$installer->startSetup();
$connection = $installer->getConnection();

//Drop entity_type_id column for wee tax table
$connection->dropColumn($installer->getTable('weee_tax'), 'entity_type_id');

$installer->endSetup();
