<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
