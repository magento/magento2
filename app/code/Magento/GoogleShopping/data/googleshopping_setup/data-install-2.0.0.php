<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\GoogleShopping\Model\Resource\Setup */
$installer = $this;

if ($installer->getModuleManager()->isEnabled('Magento_GoogleBase')) {
    $typesInsert = $installer->getConnection()->select()->from(
        $installer->getTable('googlebase_types'),
        ['type_id', 'attribute_set_id', 'target_country', 'category' => new \Zend_Db_Expr('NULL')]
    )->insertFromSelect(
        $installer->getTable('googleshopping_types')
    );

    $itemsInsert = $installer->getConnection()->select()->from(
        $installer->getTable('googlebase_items'),
        ['item_id', 'type_id', 'product_id', 'gbase_item_id', 'store_id', 'published', 'expires']
    )->insertFromSelect(
        $installer->getTable('googleshopping_items')
    );

    $attributes = '';
    foreach ($this->_configFactory->create()->getAttributes() as $destAttribtues) {
        foreach ($destAttribtues as $code => $info) {
            $attributes .= "'{$code}',";
        }
    }
    $attributes = rtrim($attributes, ',');
    $attributesInsert = $installer->getConnection()->select()->from(
        $installer->getTable('googlebase_attributes'),
        [
            'id',
            'attribute_id',
            'gbase_attribute' => new \Zend_Db_Expr("IF(gbase_attribute IN ({$attributes}), gbase_attribute, '')"),
            'type_id'
        ]
    )->insertFromSelect(
        $installer->getTable('googleshopping_attributes')
    );

    $installer->run($typesInsert);
    $installer->run($attributesInsert);
    $installer->run($itemsInsert);
}
