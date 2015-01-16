<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$installer = $this;
/* @var $installer \Magento\Catalog\Model\Resource\Setup */

$installer->startSetup();

$table = $installer->getConnection()
    ->dropColumn(
        $installer->getTable('catalog_eav_attribute'),
        'is_configurable'

    );

$installer->endSetup();
