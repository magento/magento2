<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>'
        . '<view id="view_one" class="Ogogo\Class\One" group="some_view_group"><!--comment-->'
        . '<subscriptions><table name="some_entity" entity_column="entity_id" />'
        . '<table name="some_product_relation" entity_column="product_id" /><nottable/>'
        . '<!--comment--></subscriptions></view></config>',
    'expected' => [
        'view_one' => [
            'view_id' => 'view_one',
            'action_class' => 'Ogogo\Class\One',
            'group' => 'some_view_group',
            'subscriptions' => [
                'some_entity' => ['name' => 'some_entity', 'column' => 'entity_id'],
                'some_product_relation' => ['name' => 'some_product_relation', 'column' => 'product_id'],
            ],
        ],
    ]
];
