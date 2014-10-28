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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
$attributeId = $installer->getAttributeId($entityTypeId, 'filter_price_range');
$attributeTableOld = $installer->getAttributeTable($entityTypeId, $attributeId);

$installer->updateAttribute($entityTypeId, $attributeId, 'backend_type', 'decimal');

$attributeTableNew = $installer->getAttributeTable($entityTypeId, $attributeId);

if ($attributeTableOld != $attributeTableNew) {
    $connection->disableTableKeys($attributeTableOld)->disableTableKeys($attributeTableNew);

    $select = $connection->select()->from(
        $attributeTableOld,
        array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value')
    )->where(
        'entity_type_id = ?',
        $entityTypeId
    )->where(
        'attribute_id = ?',
        $attributeId
    );

    $query = $select->insertFromSelect(
        $attributeTableNew,
        array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value')
    );

    $connection->query($query);

    $connection->delete(
        $attributeTableOld,
        $connection->quoteInto(
            'entity_type_id = ?',
            $entityTypeId
        ) . $connection->quoteInto(
            ' AND attribute_id = ?',
            $attributeId
        )
    );

    $connection->enableTableKeys($attributeTableOld)->enableTableKeys($attributeTableNew);
}

$installer->endSetup();
