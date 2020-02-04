<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'indexer_4' => [
        'indexer_id' => 'indexer_4',
        'view_id' => 'indexer_4',
        'primary' => null,
        'action_class' => 'Magento\Module\IndexerFourth',
        'shared_index' => null,
        'title' => '',
        'description' => '',
        'dependencies' => [],
    ],
    'indexer_2' => [
        'indexer_id' => 'indexer_2',
        'view_id' => 'indexer_2',
        'primary' => null,
        'action_class' => 'Magento\Module\IndexerSecond',
        'shared_index' => null,
        'title' => '',
        'description' => '',
        'fieldsets' => [],
        'dependencies' => [
            'indexer_4'
        ],
    ],
    'catalogsearch_fulltext' =>  [
        'indexer_id' => 'catalogsearch_fulltext',
        'shared_index' => null,
        'primary' => 'first',
        'view_id' => 'catalogsearch_fulltext',
        'action_class' => \Magento\CatalogSearch\Model\Indexer\Fulltext::class,
        'title' => __('Catalog Search'),
        'description' => __('Rebuild Catalog product fulltext search index'),
        'fieldsets' => [
            [
                'source' => 'MagentoModule\\ServiceClassOrRepositoryClass',
                'name' => 'first',
                'provider' => \Magento\Indexer\Model\Fieldset\ProductFieldset::class,
                'fields' => [
                    'title_alias' => [
                        'name' => 'title_alias',
                        'handler' => null,
                        'origin' => 'title',
                        'dataType' => 'text',
                        'type' => 'searchable',
                        'filters' =>
                            [
                                0 => \Magento\Framework\Search\Index\Filter\LowercaseFilter::class,
                            ],
                    ],
                    'identifier' => [
                        'name' => 'identifier',
                        'handler' => \Magento\Framework\Search\Index\Handler::class,
                        'origin' => 'identifier',
                        'dataType' => null,
                        'type' => 'filterable',
                        'filters' =>
                            [],
                    ],
                ],
            ],
            [
                'source' => 'MagentoModule\\ServiceClassOrRepositoryClass',
                'name' => 'second',
                'provider' => null,
                'fields' => [
                    'title' =>
                        [
                            'name' => 'title',
                            'handler' => null,
                            'origin' => 'title',
                            'dataType' => null,
                            'type' => 'searchable',
                            'filters' =>
                                [],
                        ],
                ],
                'references' => [
                    'first' =>
                        [
                            'fieldset' => 'first',
                            'from' => 'id_field',
                            'to' => 'second_entity_id',
                        ]
                ],
            ],
        ],
        'saveHandler' => \Magento\Cms\Model\Indexer\StoreResource::class,
        'structure' => \Magento\Cms\Model\Indexer\IndexStructure::class,
        'dependencies' => ['indexer_2'],
    ],
    'indexer_3' => [
        'indexer_id' => 'indexer_3',
        'view_id' => 'indexer_3',
        'primary' => null,
        'action_class' => 'Magento\Module\IndexerThird',
        'shared_index' => null,
        'title' => '',
        'description' => '',
        'dependencies' => [],
    ],
];
