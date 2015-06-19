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
                    'first' =>
                        [
                            'source' => 'MagentoModule\\ServiceClassOrRepositoryClass',
                            'provider' => 'Magento\\Indexer\\Model\\Fieldset\\ProductFieldset',
                            'fields' =>
                                [
                                    'title_alias' =>
                                        [
                                            'name' => 'title_alias',
                                            'handler' => NULL,
                                            'origin' => 'title',
                                            'dataType' => 'text',
                                            'type' => 'searchable',
                                            'filters' =>
                                                [
                                                    0 => 'Magento\\Framework\\Search\\Index\\Filter\\LowercaseFilter',
                                                ],
                                            'reference' =>
                                                [],
                                        ],
                                    'identifier' =>
                                        [
                                            'name' => 'identifier',
                                            'handler' => 'Magento\\Framework\\Search\\Index\\Handler',
                                            'origin' => 'identifier',
                                            'dataType' => NULL,
                                            'type' => 'filterable',
                                            'filters' =>
                                                [],
                                            'reference' =>
                                                [],
                                        ],
                                ],
                        ],
                    'second' =>
                        [
                            'source' => 'MagentoModule\\ServiceClassOrRepositoryClass',
                            'provider' => NULL,
                            'fields' =>
                                [
                                    'title' =>
                                        [
                                            'name' => 'title',
                                            'handler' => NULL,
                                            'origin' => 'title',
                                            'dataType' => NULL,
                                            'type' => 'searchable',
                                            'filters' =>
                                                [],
                                            'reference' =>
                                                [],
                                        ],
                                ],
                            'reference' =>
                                [
                                    'fieldset' => 'first',
                                    'from' => 'id_field2',
                                    'to' => 'second_entity_id2',
                                ],
                        ],
                ],
            'saveHandler' => 'Magento\\Cms\\Model\\Indexer\\StoreResource',
            'structure' => 'Magento\\Cms\\Model\\Indexer\\IndexStructure',
        ],
];
