<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Setup;

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

        $installer->startSetup();

        /**
         * Create table 'newsletter_subscriber'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('newsletter_subscriber'))
            ->addColumn(
                'subscriber_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Subscriber Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Store Id'
            )
            ->addColumn(
                'change_status_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Change Status At'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Id'
            )
            ->addColumn(
                'subscriber_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                ['nullable' => true, 'default' => null],
                'Subscriber Email'
            )
            ->addColumn(
                'subscriber_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Subscriber Status'
            )
            ->addColumn(
                'subscriber_confirm_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['default' => 'NULL'],
                'Subscriber Confirm Code'
            )
            ->addIndex(
                $installer->getIdxName('newsletter_subscriber', ['customer_id']),
                ['customer_id']
            )
            ->addIndex(
                $installer->getIdxName('newsletter_subscriber', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_subscriber', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Newsletter Subscriber');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'newsletter_template'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('newsletter_template'))
            ->addColumn(
                'template_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Template ID'
            )
            ->addColumn(
                'template_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                [],
                'Template Code'
            )
            ->addColumn(
                'template_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Template Text'
            )
            ->addColumn(
                'template_styles',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Template Styles'
            )
            ->addColumn(
                'template_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Template Type'
            )
            ->addColumn(
                'template_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Template Subject'
            )
            ->addColumn(
                'template_sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Template Sender Name'
            )
            ->addColumn(
                'template_sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Template Sender Email'
            )
            ->addColumn(
                'template_actual',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '1'],
                'Template Actual'
            )
            ->addColumn(
                'added_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Added At'
            )
            ->addColumn(
                'modified_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Modified At'
            )
            ->addIndex(
                $installer->getIdxName('newsletter_template', ['template_actual']),
                ['template_actual']
            )
            ->addIndex(
                $installer->getIdxName('newsletter_template', ['added_at']),
                ['added_at']
            )
            ->addIndex(
                $installer->getIdxName('newsletter_template', ['modified_at']),
                ['modified_at']
            )
            ->setComment('Newsletter Template');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'newsletter_queue'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('newsletter_queue'))
            ->addColumn(
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Queue Id'
            )
            ->addColumn(
                'template_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Template ID'
            )
            ->addColumn(
                'newsletter_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Newsletter Type'
            )
            ->addColumn(
                'newsletter_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Newsletter Text'
            )
            ->addColumn(
                'newsletter_styles',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Newsletter Styles'
            )
            ->addColumn(
                'newsletter_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Newsletter Subject'
            )
            ->addColumn(
                'newsletter_sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Newsletter Sender Name'
            )
            ->addColumn(
                'newsletter_sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Newsletter Sender Email'
            )
            ->addColumn(
                'queue_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Queue Status'
            )
            ->addColumn(
                'queue_start_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Queue Start At'
            )
            ->addColumn(
                'queue_finish_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Queue Finish At'
            )
            ->addIndex(
                $installer->getIdxName('newsletter_queue', ['template_id']),
                ['template_id']
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_queue', 'template_id', 'newsletter_template', 'template_id'),
                'template_id',
                $installer->getTable('newsletter_template'),
                'template_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Newsletter Queue');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'newsletter_queue_link'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('newsletter_queue_link'))
            ->addColumn(
                'queue_link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Queue Link Id'
            )
            ->addColumn(
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Queue Id'
            )
            ->addColumn(
                'subscriber_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Subscriber Id'
            )
            ->addColumn(
                'letter_sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Letter Sent At'
            )
            ->addIndex(
                $installer->getIdxName('newsletter_queue_link', ['subscriber_id']),
                ['subscriber_id']
            )
            ->addIndex(
                $installer->getIdxName('newsletter_queue_link', ['queue_id', 'letter_sent_at']),
                ['queue_id', 'letter_sent_at']
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_queue_link', 'queue_id', 'newsletter_queue', 'queue_id'),
                'queue_id',
                $installer->getTable('newsletter_queue'),
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'newsletter_queue_link',
                    'subscriber_id',
                    'newsletter_subscriber',
                    'subscriber_id'
                ),
                'subscriber_id',
                $installer->getTable('newsletter_subscriber'),
                'subscriber_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Newsletter Queue Link');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'newsletter_queue_store_link'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('newsletter_queue_store_link'))
            ->addColumn(
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Queue Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Store Id'
            )
            ->addIndex(
                $installer->getIdxName('newsletter_queue_store_link', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_queue_store_link', 'queue_id', 'newsletter_queue', 'queue_id'),
                'queue_id',
                $installer->getTable('newsletter_queue'),
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_queue_store_link', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Newsletter Queue Store Link');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'newsletter_problem'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('newsletter_problem'))
            ->addColumn(
                'problem_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Problem Id'
            )
            ->addColumn(
                'subscriber_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Subscriber Id'
            )
            ->addColumn(
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Queue Id'
            )
            ->addColumn(
                'problem_error_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Problem Error Code'
            )
            ->addColumn(
                'problem_error_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Problem Error Text'
            )
            ->addIndex(
                $installer->getIdxName('newsletter_problem', ['subscriber_id']),
                ['subscriber_id']
            )
            ->addIndex(
                $installer->getIdxName('newsletter_problem', ['queue_id']),
                ['queue_id']
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_problem', 'queue_id', 'newsletter_queue', 'queue_id'),
                'queue_id',
                $installer->getTable('newsletter_queue'),
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('newsletter_problem', 'subscriber_id', 'newsletter_subscriber', 'subscriber_id'),
                'subscriber_id',
                $installer->getTable('newsletter_subscriber'),
                'subscriber_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Newsletter Problems');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
