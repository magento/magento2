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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer  \Magento\Eav\Model\Entity\Setup*/
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
$tableName = $installer->getTable('eav_attribute_group');

$connection->addColumn($tableName, 'attribute_group_code', array(
    'type' => \Magento\DB\Ddl\Table::TYPE_TEXT,
    'length' => '255',
    'comment' => 'Attribute Group Code',
));

$connection->addColumn($tableName, 'tab_group_code', array(
    'type' => \Magento\DB\Ddl\Table::TYPE_TEXT,
    'length' => '255',
    'comment' => 'Tab Group Code',
));

/** @var $groups \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection*/
$groups = $installer->getAttributeGroupCollectionFactory();
foreach ($groups as $group) {
    /** @var $group \Magento\Eav\Model\Entity\Attribute\Group*/
    $group->save();
}

$installer->endSetup();
