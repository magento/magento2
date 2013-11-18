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
 * @category    Magento
 * @package     Magento_GiftMessage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/** @var $installer \Magento\GiftMessage\Model\Resource\Setup */

$installer = $this;
$installer->startSetup();

/**
 * Create table 'gift_message'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift_message'))
    ->addColumn('gift_message_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'GiftMessage Id')
    ->addColumn('customer_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer id')
    ->addColumn('sender', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
        ), 'Sender')
    ->addColumn('recipient', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
        ), 'Recipient')
    ->addColumn('message', \Magento\DB\Ddl\Table::TYPE_TEXT, null, array(
        ), 'Message')
    ->setComment('Gift Message');

$installer->getConnection()->createTable($table);

/**
 * Add 'gift_message_id' attributes for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => \Magento\DB\Ddl\Table::TYPE_INTEGER,
    'visible'  => false,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'gift_message_id', $options);
}

/**
 * Add 'gift_message_available' attributes for entities
 */
$installer->addAttribute('order_item', 'gift_message_available', $options);
$installer->createGiftMessageSetup(array('resourceName' => 'catalog_setup'))->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY, 'gift_message_available',
    array(
        'group'         => 'Gift Options',
        'backend'       => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
        'frontend'      => '',
        'label'         => 'Allow Gift Message',
        'input'         => 'select',
        'class'         => '',
        'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
        'global'        => true,
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,
        'default'       => '',
        'apply_to'      => '',
        'input_renderer'   => 'Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config',
        'is_configurable'  => 0,
        'visible_on_front' => false
    )
);

$installer->endSetup();
