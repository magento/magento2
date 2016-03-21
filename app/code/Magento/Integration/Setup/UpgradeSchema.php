<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class for integration tables schema upgrades
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            /**
             * Create table 'oauth_token_request_log'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('oauth_token_request_log')
            )->addColumn(
                'user_login',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'primary' => true],
                'Customer email or admin login'
            )->addColumn(
                'user_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'User type (admin or customer)'
            )->addColumn(
                'failures_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => 0],
                'Number of failed authentication attempts in a row'
            )->addColumn(
                'lock_expires_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Lock expiration time'
            )->addIndex(
                $setup->getIdxName('oauth_token_request_log', ['user_login', 'user_type']),
                ['user_login', 'user_type']
            )->setComment(
                'Log of token request authentication failures.'
            );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
