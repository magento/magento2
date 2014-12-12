<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$connection->update($installer->getTable('core_theme'), ['area' => 'frontend'], ['area = ?' => '']);

$installer->endSetup();
$installer->getEventManager()->dispatch('theme_registration_from_filesystem');
