<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

$this->startSetup();

/**
 * Update 'customer_visitor' table.
 */

$this->getConnection()
    ->addColumn(
        $this->getTable('customer_visitor'),
        'customer_id',
        [
            'type' => Table::TYPE_INTEGER,
            'after' => 'visitor_id',
            'comment' => 'Customer ID'
        ]
    );

$this->getConnection()
    ->addIndex(
        $this->getTable('customer_visitor'),
        $this->getIdxName(
            $this->getTable('customer_visitor'),
            ['customer_id']
        ),
        'customer_id'
    );

/**
 * Create 'customer_log' table.
 */

$table = $this->getConnection()
    ->newTable(
        $this->getTable('customer_log')
    )
    ->addColumn(
        'log_id',
        Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false,
            'identity' => true,
            'primary' => true
        ],
        'Log ID'
    )
    ->addColumn(
        'customer_id',
        Table::TYPE_INTEGER,
        null,
        [
            'nullable' => false
        ],
        'Customer ID'
    )
    ->addColumn(
        'last_login_at',
        Table::TYPE_TIMESTAMP,
        null,
        [
            'nullable' => false
        ],
        'Last Login Time'
    )
    ->addColumn(
        'last_logout_at',
        Table::TYPE_TIMESTAMP,
        null,
        [
            'nullable' => true,
            'default' => null
        ],
        'Last Logout Time'
    )
    ->addIndex(
        $this->getIdxName(
            $this->getTable('customer_log'),
            ['customer_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['customer_id'],
        [
            'type' => AdapterInterface::INDEX_TYPE_UNIQUE
        ]
    )
    ->setComment('Customer Log Table');

$this->getConnection()->createTable($table);

$this->endSetup();
