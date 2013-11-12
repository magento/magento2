<?php
/**
 * Upgrade script for integration table creation.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var \Magento\Core\Model\Resource\Setup $installer */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('integration'))
    ->addColumn(
        'integration_id',
        \Magento\DB\Ddl\Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Integration ID'
    )
    ->addColumn(
        'name',
        \Magento\DB\Ddl\Table::TYPE_TEXT,
        255,
        array(
            'nullable' => false,
        ),
        'Integration name is displayed in the admin interface'
    )
    ->addColumn(
        'email',
        \Magento\DB\Ddl\Table::TYPE_TEXT,
        255,
        array(
            'nullable' => false,
        ),
        'Email address of the contact person'
    )
    ->addColumn(
        'authentication',
        \Magento\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false
        ),
        'Authentication mechanism'
    )
    ->addColumn(
        'endpoint',
        \Magento\DB\Ddl\Table::TYPE_TEXT,
        255,
        array(
            'nullable' => false,
        ),
        'Endpoint for Oauth handshake'
    )
    ->addColumn(
        'status',
        \Magento\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false
        ),
        'Integration status'
    )
    ->addColumn(
        'created_at',
        \Magento\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        array('nullable' => false),
        'Creation Time'
    )
    ->addColumn(
        'updated_at',
        \Magento\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        array('nullable' => false),
        'Update Time'
    )
    ->addIndex(
        $installer->getIdxName(
            'integration',
            array('name'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('name'),
        array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
    );
$installer->getConnection()->createTable($table);

$installer->endSetup();
