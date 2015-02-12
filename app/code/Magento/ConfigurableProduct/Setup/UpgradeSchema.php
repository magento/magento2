<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleSchemaResourceInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function upgrade(ModuleSchemaResourceInterface $setup, ModuleContextInterface $context)
	{
        if (version_compare($context->getVersion(), '2.0.1') <= 0) {
            $installer = $setup;
		
		    $installer->startSetup();

            $table = $installer->getConnection()
                ->dropColumn(
                    $installer->getTable('catalog_eav_attribute'),
                    'is_configurable'
                );

            $installer->endSetup();
        }
	}
}