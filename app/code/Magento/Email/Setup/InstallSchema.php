<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'email_template'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('email_template')
        )->addColumn(
            'template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Template ID'
        )->addColumn(
            'template_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            150,
            ['nullable' => false],
            'Template Name'
        )->addColumn(
            'template_text',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => false],
            'Template Content'
        )->addColumn(
            'template_styles',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Templste Styles'
        )->addColumn(
            'template_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Template Type'
        )->addColumn(
            'template_subject',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            200,
            ['nullable' => false],
            'Template Subject'
        )->addColumn(
            'template_sender_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            200,
            [],
            'Template Sender Name'
        )->addColumn(
            'template_sender_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            200,
            [],
            'Template Sender Email'
        )->addColumn(
            'added_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Date of Template Creation'
        )->addColumn(
            'modified_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Date of Template Modification'
        )->addColumn(
            'orig_template_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            200,
            [],
            'Original Template Code'
        )->addColumn(
            'orig_template_variables',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Original Template Variables'
        )->addIndex(
            $installer->getIdxName(
                'email_template',
                ['template_code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['template_code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('email_template', ['added_at']),
            ['added_at']
        )->addIndex(
            $installer->getIdxName('email_template', ['modified_at']),
            ['modified_at']
        )->setComment(
            'Email Templates'
        );

        $installer->getConnection()->createTable($table);

    }
}
