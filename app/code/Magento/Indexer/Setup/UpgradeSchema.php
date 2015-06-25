<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\DB\Ddl\Table;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            /**
             * Add hash column of indexer_state table.
             */
            $table = $setup->getTable('indexer_state');
            $connection->addColumn(
                $table,
                'hash_config',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => false,
                    'comment' => 'Hash of indexer config',
                ]
            );
        }
    }
}
