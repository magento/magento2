<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>' .
    '<indexer id="indexer_internal_name" view_id="view01" class="Index\Class\Name">' .
    '<title translate="true">' .
    'Indexer public name</title><description translate="true">Indexer public description</description>'
    . '<dependencies><indexer id="test_indexer_with_dependencies"/></dependencies>'
    . '</indexer>'
    . '<indexer id="test_indexer_with_dependencies" class="Index\Temp">'
    . '<dependencies><indexer id="test_indexer"/></dependencies>'
    . '</indexer>'
    . '<indexer id="test_indexer">'
    . 'Indexer public description'
    . '</indexer>'
    . '</config>',
    'expected' => [
        'test_indexer' => [
            'indexer_id' => 'test_indexer',
            'view_id' => '',
            'action_class' => '',
            'title' => '',
            'description' => '',
            'primary' => null,
            'shared_index' => null,
            'dependencies' => [],
        ],
        'test_indexer_with_dependencies' => [
            'indexer_id' => 'test_indexer_with_dependencies',
            'view_id' => '',
            'action_class' => 'Index\Temp',
            'title' => '',
            'description' => '',
            'primary' => null,
            'shared_index' => null,
            'fieldsets' => [],
            'dependencies' => ['test_indexer'],
        ],
        'indexer_internal_name' => [
            'indexer_id' => 'indexer_internal_name',
            'view_id' => 'view01',
            'action_class' => 'Index\Class\Name',
            'title' => __('Indexer public name'),
            'description' => __('Indexer public description'),
            'primary' => null,
            'shared_index' => null,
            'fieldsets' => [],
            'dependencies' => [
                'test_indexer_with_dependencies'
            ],
        ],
    ]
];
