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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>'
        . '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><!--comment-->'
        . '<subscriptions><table name="some_entity" entity_column="entity_id" />'
        . '<table name="some_product_relation" entity_column="product_id" /><nottable/>'
        . '<!--comment--></subscriptions></view></config>',
    'expected' => array(
        'view_one' => array(
            'view_id' => 'view_one',
            'action_class' => 'Ogogo\Class\One',
            'group' => 'some_view_group',
            'subscriptions' => array(
                'some_entity' => array('name' => 'some_entity', 'column' => 'entity_id'),
                'some_product_relation' => array('name' => 'some_product_relation', 'column' => 'product_id')
            )
        )
    )
);
