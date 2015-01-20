<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$installer = $this;
/** @var $installer \Magento\Setup\Module\SetupModule */
$installer->startSetup();
$connection = $installer->getConnection();

//Drop entity_type_id column for wee tax table
$connection->dropColumn($installer->getTable('weee_tax'), 'entity_type_id');

$installer->endSetup();
