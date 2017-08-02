<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'widget'
         */
        if (!$installer->getConnection()->isTableExists($installer->getTable('widget'))) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('widget')
            )->addColumn(
                'widget_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Widget Id'
            )->addColumn(
                'widget_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Widget code for template directive'
            )->addColumn(
                'widget_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Widget Type'
            )->addColumn(
                'parameters',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Parameters'
            )->addIndex(
                $installer->getIdxName('widget', 'widget_code'),
                'widget_code'
            )->setComment(
                'Preconfigured Widgets'
            );
            $installer->getConnection()->createTable($table);
        } else {
            $installer->getConnection()->dropIndex($installer->getTable('widget'), 'IDX_CODE');

            $tables = [
                $installer->getTable(
                    'widget'
                ) => [
                    'columns' => [
                        'widget_id' => [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true,
                            'comment' => 'Widget Id',
                        ],
                        'parameters' => [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => '64K',
                            'comment' => 'Parameters',
                        ],
                    ],
                    'comment' => 'Preconfigured Widgets',
                ],
            ];

            $installer->getConnection()->modifyTables($tables);

            $installer->getConnection()->changeColumn(
                $installer->getTable('widget'),
                'code',
                'widget_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Widget code for template directive'
                ]
            );

            $installer->getConnection()->changeColumn(
                $installer->getTable('widget'),
                'type',
                'widget_type',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'comment' => 'Widget Type']
            );

            $installer->getConnection()->addIndex(
                $installer->getTable('widget'),
                $installer->getIdxName('widget', ['widget_code']),
                ['widget_code']
            );
        }

        /**
         * Create table 'widget_instance'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('widget_instance')
        )->addColumn(
            'instance_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Instance Id'
        )->addColumn(
            'instance_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Instance Type'
        )->addColumn(
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Theme id'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Widget Title'
        )->addColumn(
            'store_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => '0'],
            'Store ids'
        )->addColumn(
            'widget_parameters',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Widget parameters'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort order'
        )->addForeignKey(
            $installer->getFkName('widget_instance', 'theme_id', 'theme', 'theme_id'),
            'theme_id',
            $installer->getTable('theme'),
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Instances of Widget for Package Theme'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'widget_instance_page'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('widget_instance_page')
        )->addColumn(
            'page_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Page Id'
        )->addColumn(
            'instance_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Instance Id'
        )->addColumn(
            'page_group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            25,
            [],
            'Block Group Type'
        )->addColumn(
            'layout_handle',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Layout Handle'
        )->addColumn(
            'block_reference',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Container'
        )->addColumn(
            'page_for',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            25,
            [],
            'For instance entities'
        )->addColumn(
            'entities',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Catalog entities (comma separated)'
        )->addColumn(
            'page_template',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Path to widget template'
        )->addIndex(
            $installer->getIdxName('widget_instance_page', 'instance_id'),
            'instance_id'
        )->addForeignKey(
            $installer->getFkName('widget_instance_page', 'instance_id', 'widget_instance', 'instance_id'),
            'instance_id',
            $installer->getTable('widget_instance'),
            'instance_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Instance of Widget on Page'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'widget_instance_page_layout'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('widget_instance_page_layout')
        )->addColumn(
            'page_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Page Id'
        )->addColumn(
            'layout_update_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Layout Update Id'
        )->addIndex(
            $installer->getIdxName('widget_instance_page_layout', 'page_id'),
            'page_id'
        )->addIndex(
            $installer->getIdxName(
                'widget_instance_page_layout',
                ['layout_update_id', 'page_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['layout_update_id', 'page_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('widget_instance_page_layout', 'page_id', 'widget_instance_page', 'page_id'),
            'page_id',
            $installer->getTable('widget_instance_page'),
            'page_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'widget_instance_page_layout',
                'layout_update_id',
                'layout_update',
                'layout_update_id'
            ),
            'layout_update_id',
            $installer->getTable('layout_update'),
            'layout_update_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Layout updates'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'layout_update'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('layout_update')
        )->addColumn(
            'layout_update_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Layout Update Id'
        )->addColumn(
            'handle',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Handle'
        )->addColumn(
            'xml',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Xml'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Last Update Timestamp'
        )->addIndex(
            $installer->getIdxName('layout_update', ['handle']),
            ['handle']
        )->setComment(
            'Layout Updates'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'layout_link'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('layout_link')
        )->addColumn(
            'layout_link_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Link Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Theme id'
        )->addColumn(
            'layout_update_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Layout Update Id'
        )->addColumn(
            'is_temporary',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => '0'],
            'Defines whether Layout Update is Temporary'
        )->addIndex(
            $installer->getIdxName('layout_link', ['layout_update_id']),
            ['layout_update_id']
        )->addForeignKey(
            $installer->getFkName('layout_link', 'layout_update_id', 'layout_update', 'layout_update_id'),
            'layout_update_id',
            $installer->getTable('layout_update'),
            'layout_update_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName(
                'layout_link',
                ['store_id', 'theme_id', 'layout_update_id', 'is_temporary'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['store_id', 'theme_id', 'layout_update_id', 'is_temporary']
        )->addForeignKey(
            $installer->getFkName('layout_link', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('layout_link', 'theme_id', 'theme', 'theme_id'),
            'theme_id',
            $installer->getTable('theme'),
            'theme_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Layout Link'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
