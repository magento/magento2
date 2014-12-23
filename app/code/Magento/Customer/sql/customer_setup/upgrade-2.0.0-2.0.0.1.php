<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->getConnection()->dropColumn($installer->getTable('customer_address_entity'), 'entity_type_id');
$installer->getConnection()->dropColumn($installer->getTable('customer_address_entity'), 'attribute_set_id');
