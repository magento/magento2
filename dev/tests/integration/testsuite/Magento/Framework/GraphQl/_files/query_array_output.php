<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'Query' =>
        [
            'name' => 'Query',
            'type' => 'graphql_type',
            'fields' =>
                [
                    'products' =>
                        [
                            'name' => 'products',
                            'type' => 'Products',
                            'required' => false,
                            'resolver' => 'Magento\\Framework\\GraphQlPersistence\\Resolver\\Query\\Resolver',
                            'arguments' =>
                                [
                                    'search' =>
                                        [
                                            'type' => 'String',
                                            'name' => 'search',
                                            'description' => 'Text to be used in a full text search. If multiple keywords are specified, each keyword is evaluated separately.',
                                        ],

                                    'filter' =>
                                        [
                                            'type' => 'ProductFilterInput',
                                            'name' => 'filter',
                                        'description' => 'Defines which search criteria to use to find the desired results. Each filter defines the field or fields to be searched, the condition type, and the search value.',
                                    ],

                                   'pageSize' =>
                                    [
                                        'type' => 'Int',
                                        'name' => 'pageSize',
                                        'description' => 'The maximum number of items to return. If no value is specified, the search returns 20 items.',
                                    ],

                                  'currentPage' =>
                                    [
                                        'type' => 'Int',
                                        'name' => 'currentPage',
                                        'description' => 'Specifies which page of results to return. If no value is specified, the first page is returned. If you specify a value that is greater than the number of available pages, an error is returned.',
                                    ],

                                  'sort' =>
                                    [
                                        'type' => 'ProductSortInput',
                                        'name' => 'sort',
                                        'description' => 'Specifies which field or fields to use for sorting the results. If you specify more than one field, Magento sorts by the first field listed. Then, if any items have the same value, those items will be sorted by the secondary field. The value for each field can be set to either ASC (ascending) or DESC (descending).',
                                    ]
                            ]
                    ],
                    'mergedField' =>
                        [
                            'name' => 'mergedField',
                            'type' => 'Int',
                            'required' => false,
                            'resolver' => 'testResolverPath',
                            'description' => 'test field description',
                            'arguments' =>
                                [
                                    'mergedArgument' =>
                                        [
                                            'type' => 'String',
                                            'name' => 'mergedArgument',
                                            'description' => 'test argument description',
                                        ]
                                ]
                        ]
            ]
    ],
    'PriceAdjustmentDescriptionEnum' =>
        [
            'name' => 'PriceAdjustmentDescriptionEnum',
            'type' => 'graphql_enum',
            'items' =>
                [
                    'INCLUDED' =>
                        [
                            'name' => 'included',
                            '_value' => 'INCLUDED',
                        ],
                    'EXCLUDED' =>
                        [
                            'name' => 'excluded',
                            '_value' => 'EXCLUDED',
                        ],
                    'MERGEDITEM' =>
                        [
                            'name' => 'mergedItem',
                            '_value' => 'MERGEDITEM',
                        ],

                ],
        ],
    'PriceTypeEnum' =>
        [
            'name' => 'PriceTypeEnum',
            'type' => 'graphql_enum',
            'items' =>
                [
                    'FIXED' =>
                        [
                            'name' => 'fixed',
                            '_value' => 'FIXED',
                        ],
                    'PERCENT' =>
                        [
                            'name' => 'percent',
                            '_value' => 'PERCENT',
                        ],
                    'DYNAMIC' =>
                        [
                            'name' => 'dynamic',
                            '_value' => 'DYNAMIC',
                        ]
                ]
        ],
    'ProductLinks' =>
        [
            'name' => 'ProductLinks',
            'type' => 'graphql_type',
            'implements' =>
                [
                    'ProductLinksInterface' =>
                        [
                            'interface' => 'ProductLinksInterface',
                            'copyFields' => 'true',
                        ],

                     'MergedInterface' =>
                         [
                                'interface' => 'MergedInterface',
                                'copyFields' => 'true',
                            ],
                ],
            'fields' =>
                [
                    'sku' =>
                        [
                            'name' => 'sku',
                            'type' => 'String',
                            'required' => false,
                            'description' => 'The identifier of the linked product',
                            'arguments' =>
                                [
                                ],
                        ],
                    'link_type' =>
                        [
                            'name' => 'link_type',
                            'type' => 'String',
                            'required' => false,
                            'description' => 'One of \'related\', \'associated\', \'upsell\', or \'crosssell\'.',
                            'arguments' =>
                                [
                                ],
                        ],
                    'linked_product_sku' =>
                        [
                            'name' => 'linked_product_sku',
                            'type' => 'String',
                            'required' => false,
                            'description' => 'The SKU of the linked product',
                            'arguments' =>
                                [
                                ],
                        ],
                    'linked_product_type' =>
                        [
                            'name' => 'linked_product_type',
                            'type' => 'String',
                            'required' => false,
                            'description' => 'The type of linked product (\'simple\', \'virtual\', \'bundle\', \'downloadable\',\'grouped\', \'configurable\')',
                            'arguments' =>
                                [
                                ],
                        ],
                    'position' =>
                        [
                            'name' => 'position',
                            'type' => 'Int',
                            'required' => false,
                            'description' => 'The position within the list of product links',
                            'arguments' =>
                                [
                                ],
                        ],
                    'mergedFieldN' =>
                        [
                            'name' => 'mergedFieldN',
                            'type' => 'String',
                            'required' => false,
                            'description' => 'The identifier of the linked merged product',
                            'arguments' =>
                                [
                                ],
                        ],
                ],
        ]
];
