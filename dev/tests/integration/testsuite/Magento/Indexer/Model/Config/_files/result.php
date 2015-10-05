<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'catalogsearch_fulltext' =>
        [
            'indexer_id' => 'catalogsearch_fulltext',
            'primary' => 'first',
            'view_id' => 'catalogsearch_fulltext',
            'action_class' => 'Magento\\CatalogSearch\\Model\\Indexer\\Fulltext',
            'title' => __('Catalog Search'),
            'description' => __('Rebuild Catalog product fulltext search index'),
            'fieldsets' =>
                [
                    [
                        'source' => 'MagentoModule\\ServiceClassOrRepositoryClass',
                        'name' => 'first',
                        'provider' => 'Magento\\Indexer\\Model\\Fieldset\\ProductFieldset',
                        'fields' =>
                            [
                                'title_alias' =>
                                    [
                                        'name' => 'title_alias',
                                        'handler' => null,
                                        'origin' => 'title',
                                        'dataType' => 'text',
                                        'type' => 'searchable',
                                        'filters' =>
                                            [
                                                0 => 'Magento\\Framework\\Search\\Index\\Filter\\LowercaseFilter',
                                            ],
                                    ],
                                'identifier' =>
                                    [
                                        'name' => 'identifier',
                                        'handler' => 'Magento\\Framework\\Search\\Index\\Handler',
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
                        'fields' =>
                            [
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
                        'references' =>
                            [
                                'first' =>
                                    [
                                        'fieldset' => 'first',
                                        'from' => 'id_field',
                                        'to' => 'second_entity_id',
                                    ]
                            ],
                    ],
                ],
            'saveHandler' => 'Magento\\Cms\\Model\\Indexer\\StoreResource',
            'structure' => 'Magento\\Cms\\Model\\Indexer\\IndexStructure',
        ],
];
