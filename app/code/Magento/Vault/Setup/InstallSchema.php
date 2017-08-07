<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;

/**
 * Class InstallSchema
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class InstallSchema implements InstallSchemaInterface
{
    const ID_FILED_NAME = 'entity_id';

    const PAYMENT_TOKEN_TABLE = 'vault_payment_token';

    const ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE = 'vault_payment_token_order_payment_link';

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     * @since 2.1.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->startSetup()
            ->getConnection()
            ->newTable($setup->getTable(self::PAYMENT_TOKEN_TABLE))
            ->addColumn(
                InstallSchema::ID_FILED_NAME,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Customer Id'
            )->addColumn(
                'public_hash',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Hash code for using on frontend'
            )->addColumn(
                'payment_method_code',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Payment method code'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Type'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'expires_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Expires At'
            )->addColumn(
                'gateway_token',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Gateway Token'
            )->addColumn(
                'details',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Details'
            )->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'dafault' => true],
                'Is active flag'
            )->addColumn(
                'is_visible',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'dafault' => true],
                'Is visible flag'
            )->addIndex(
                $setup->getIdxName(
                    'vault_payment_token_unique_index',
                    ['payment_method_code', 'customer_id', 'gateway_token'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['payment_method_code', 'customer_id', 'gateway_token'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addIndex(
                $setup->getIdxName(
                    'vault_payment_token_hash_unique_index',
                    ['public_hash'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['public_hash'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable(self::PAYMENT_TOKEN_TABLE),
                    'customer_id',
                    $setup->getTable('customer_entity'),
                    'entity_id'
                ),
                'customer_id',
                $setup->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Vault tokens of payment');

        $setup->getConnection()->createTable($table);

        $table = $setup->startSetup()
            ->getConnection()
            ->newTable($setup->getTable(self::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE))
            ->addColumn(
                'order_payment_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Order payment Id'
            )->addColumn(
                'payment_token_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Payment token Id'
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable(self::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE),
                    'order_payment_id',
                    $setup->getTable('sales_order_payment'),
                    'entity_id'
                ),
                'order_payment_id',
                $setup->getTable('sales_order_payment'),
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable(self::ORDER_PAYMENT_TO_PAYMENT_TOKEN_TABLE),
                    'payment_token_id',
                    $setup->getTable(self::PAYMENT_TOKEN_TABLE),
                    'entity_id'
                ),
                'payment_token_id',
                $setup->getTable(self::PAYMENT_TOKEN_TABLE),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Order payments to vault token');

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
