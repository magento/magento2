<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $installer = $setup;

            $installer->startSetup();
            $connection = $installer->getConnection();

            /**
             * Remove column 'theme_version' from 'core_theme'
             */
            $connection->dropColumn(
                $installer->getTable('theme'),
                'theme_version'
            );

            $installer->endSetup();
        }
    }
}
