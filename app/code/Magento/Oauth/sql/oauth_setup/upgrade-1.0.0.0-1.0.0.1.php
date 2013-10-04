<?php
/**
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

/** @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;
$installer->startSetup();

$installer
    ->getConnection()
    ->dropIndex($installer->getTable('oauth_nonce'), $installer->getIdxName(
        'oauth_nonce',
        array('nonce'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ));

$installer
    ->getConnection()
    ->addColumn(
        $installer->getTable('oauth_nonce'),
        'consumer_id',
        array(
            'type' => \Magento\DB\Ddl\Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => false,
            'comment' => 'Consumer ID'
        ));

$keyFieldsList = array('nonce', 'consumer_id');
$installer
    ->getConnection()
    ->addIndex(
        $installer->getTable('oauth_nonce'),
        $installer->getIdxName(
            'oauth_nonce',
            $keyFieldsList,
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        $keyFieldsList,
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    );

$installer
    ->getConnection()
    ->addForeignKey(
        $installer->getFkName('oauth_nonce', 'consumer_id', 'oauth_consumer', 'entity_id'),
        $installer->getTable('oauth_nonce'),
        'consumer_id',
        $installer->getTable('oauth_consumer'),
        'entity_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\DB\Ddl\Table::ACTION_CASCADE
    );

$installer
    ->getConnection()
    ->addColumn(
        $installer->getTable('oauth_consumer'),
        'http_post_url',
        array(
            'type' => \Magento\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'comment' => 'Http Post URL'
        )
    );

$installer->endSetup();
