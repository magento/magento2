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
 * @category    Mage
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer  Mage_Eav_Model_Entity_Setup*/
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
$tableName = $installer->getTable('eav_attribute_group');

$connection->addColumn($tableName, 'attribute_group_code', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => '255',
    'comment' => 'Attribute Group Code',
));

$connection->addColumn($tableName, 'tab_group_code', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => '255',
    'comment' => 'Tab Group Code',
));

/** @var $groups Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection*/
$groups = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection');
foreach ($groups as $group) {
    /** @var $group Mage_Eav_Model_Entity_Attribute_Group*/
    $group->save();
}

$installer->endSetup();
