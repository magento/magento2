<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>' .
    '<indexer id="indexer_internal_name" view_id="view01" class="Index\Class\Name">' .
    '<title translate="true">' .
    'Indexer public name</title><description translate="true">Indexer public description</description>' .
    '</indexer><indexer id="test_indexer">' .
    'Indexer public description' .
    '</indexer></config>',
    'expected' => [
        'indexer_internal_name' => [
            'indexer_id' => 'indexer_internal_name',
            'view_id' => 'view01',
            'action_class' => 'Index\Class\Name',
            'title' => __('Indexer public name'),
            'description' => __('Indexer public description'),
            'primary' => null,
            'fieldsets' => []
        ],
        'test_indexer' => [
            'indexer_id' => 'test_indexer',
            'view_id' => '',
            'action_class' => '',
            'title' => '',
            'description' => '',
            'primary' => null,
        ],
    ]
];
