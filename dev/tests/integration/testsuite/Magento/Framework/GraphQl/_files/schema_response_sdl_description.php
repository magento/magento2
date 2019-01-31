<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        'kind' =>  'OBJECT',
        'name' =>  'Query',
        'description' =>  null,
        'fields' =>  [
            [
                'name' =>  'placeholder',
                'description' =>  'comment for placeholder.',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'products',
                'description' =>  'comment for products fields',
                'args' =>  [
                    [
                        'name' =>  'search',
                        'description' =>  '',
                        'type' =>  [
                            'kind' =>  'SCALAR',
                            'name' =>  'String',
                            'ofType' =>  null
                        ],
                        'defaultValue' =>  null
                    ],
                    [
                        'name' =>  'filter',
                        'description' =>  '',
                        'type' =>  [
                            'kind' =>  'INPUT_OBJECT',
                            'name' =>  'ProductFilterInput',
                            'ofType' =>  null
                        ],
                        'defaultValue' =>  null
                    ],
                    [
                        'name' =>  'pageSize',
                        'description' =>  '',
                        'type' =>  [
                            'kind' =>  'SCALAR',
                            'name' =>  'Int',
                            'ofType' =>  null
                        ],
                        'defaultValue' =>  null
                    ],
                    [
                        'name' =>  'currentPage',
                        'description' =>  '',
                        'type' =>  [
                            'kind' =>  'SCALAR',
                            'name' =>  'Int',
                            'ofType' =>  null
                        ],
                        'defaultValue' =>  null
                    ],
                    [
                        'name' =>  'sort',
                        'description' =>  '',
                        'type' =>  [
                            'kind' =>  'INPUT_OBJECT',
                            'name' =>  'ProductSortInput',
                            'ofType' =>  null
                        ],
                        'defaultValue' =>  null
                    ]
                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'Products',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'SCALAR',
        'name' =>  'String',
        'description' => 'The `String` scalar type represents textual data, represented as UTF-8' . "\n" .
            'character sequences. The String type is most often used by GraphQL to'. "\n" .
            'represent free-form human-readable text.',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'INPUT_OBJECT',
        'name' =>  'ProductFilterInput',
        'description' =>  'Comment for ProductFilterInput',
        'fields'  =>   null,
        'inputFields' =>  [
            [
                'name' =>  'name',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'sku',
                'description' =>  'Comment for field_sku which is of type FilterTypeInput',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'description',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'short_description',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'price',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'special_price',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'special_from_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'special_to_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'weight',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'manufacturer',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'meta_title',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'meta_keyword',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'meta_description',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'image',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'small_image',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'thumbnail',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'tier_price',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'news_from_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'news_to_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_design',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_design_from',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_design_to',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_layout_update',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'page_layout',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'category_ids',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'options_container',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'required_options',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'has_options',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'image_label',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'small_image_label',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'thumbnail_label',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'created_at',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'updated_at',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'country_of_manufacture',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_layout',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'gift_message_available',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'FilterTypeInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'or',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'INPUT_OBJECT',
                    'name' =>  'ProductFilterInput',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ]
        ],
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'INPUT_OBJECT',
        'name' =>  'FilterTypeInput',
        'description' =>  'Comment for FilterTypeInput',
        'fields' =>  null,
        'inputFields' =>  [
            [
                'name' =>  'eq',
                'description' =>  'Equal',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'finset',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'SCALAR',
                        'name' =>  'String',
                        'ofType' =>  null
                    ]
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'from',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'gt',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'gteq',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'in',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'SCALAR',
                        'name' =>  'String',
                        'ofType' =>  null
                    ]
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'like',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'lt',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'lteq',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'moreq',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'neq',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'notnull',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'null',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'to',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'nin',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'SCALAR',
                        'name' =>  'String',
                        'ofType' =>  null
                    ]
                ],
                'defaultValue' =>  null
            ]
        ],
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'SCALAR',
        'name' =>  'Int',
        'description' =>  'The `Int` scalar type represents non-fractional signed whole numeric' . "\n" .
            'values. Int can represent values between -(2^31) and 2^31 - 1. ',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'INPUT_OBJECT',
        'name' =>  'ProductSortInput',
        'description' =>  'Input ProductSortInput',
        'fields' =>  null,
        'inputFields' =>  [
            [
                'name' =>  'name',
                'description' =>  'Name',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'sku',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'description',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'short_description',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'price',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'special_price',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'special_from_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'special_to_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'weight',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'manufacturer',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'meta_title',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'meta_keyword',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'meta_description',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'image',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'small_image',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'thumbnail',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'tier_price',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'news_from_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'news_to_date',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_design',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_design_from',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_design_to',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_layout_update',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'page_layout',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'category_ids',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'options_container',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'required_options',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'has_options',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'image_label',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'small_image_label',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'thumbnail_label',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'created_at',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'updated_at',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'country_of_manufacture',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'custom_layout',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ],
            [
                'name' =>  'gift_message_available',
                'description' =>  '',
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'SortEnum',
                    'ofType' =>  null
                ],
                'defaultValue' =>  null
            ]
        ],
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'ENUM',
        'name' =>  'SortEnum',
        'description' =>  'Comment for SortEnum.',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'ASC',
                'description' =>  'Ascending Order',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'DESC',
                'description' =>  'Descending Order',
                'isDeprecated' =>  false
            ]
        ],
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'Products',
        'description' =>  'Comment for Products',
        'fields' =>  [
            [
                'name' =>  'items',
                'description' =>  'comment for items[Products].',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'INTERFACE',
                        'name' =>  'ProductInterface',
                        'ofType' =>  null
                    ]
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'page_info',
                'description' =>  'comment for page_info.',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'SearchResultPageInfo',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'total_count',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind'=> 'INTERFACE',
        'name'=> 'ProductInterface',
        'description'=> 'comment for ProductInterface',
        'fields'=> [
            [
                'name'=> 'url_key',
                'description'=> 'comment for url_key inside ProductInterface type.',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'url_path',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'id',
                'description'=> 'comment for [ProductInterface].',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'name',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'special_price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'special_from_date',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'attribute_set_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'tier_price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'category_ids',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'SCALAR',
                        'name'=> 'Int',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'updated_at',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'country_of_manufacture',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'type_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'website_ids',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'SCALAR',
                        'name'=> 'Int',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'category_links',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'ProductCategoryLinks',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_links',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'INTERFACE',
                        'name'=> 'ProductLinksInterface',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'media_gallery_entries',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'MediaGalleryEntry',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'tier_prices',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'ProductTierPrices',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'ProductPrices',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'manufacturer',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> null,
        'enumValues'=> null,
        'possibleTypes'=> [
            [
                'kind'=> 'OBJECT',
                'name'=> 'SimpleProduct',
                'ofType'=> null
            ],
            [
                'kind'=> 'OBJECT',
                'name'=> 'VirtualProduct',
                'ofType'=> null
            ]
        ]
    ],
    [
        'kind' =>  'SCALAR',
        'name' =>  'Float',
        'description' =>  'The `Float` scalar type represents signed double-precision fractional' . "\n" .
            'values as specified by' . "\n" .
            '[IEEE 754](http://en.wikipedia.org/wiki/IEEE_floating_point). ',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'ProductCategoryLinks',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'position',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'category_id',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'INTERFACE',
        'name' =>  'ProductLinksInterface',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'sku',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'link_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'linked_product_sku',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'linked_product_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'position',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  [
            [
                'kind' =>  'OBJECT',
                'name' =>  'ProductLinks',
                'ofType' =>  null
            ]
        ]
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'MediaGalleryEntry',
        'description' =>  'Comment for MediaGalleryEntry type',
        'fields' =>  [
            [
                'name' =>  'id',
                'description' =>  'id for MediaGalleryEntry',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'media_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'label',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'position',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'disabled',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Boolean',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'types',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'SCALAR',
                        'name' =>  'String',
                        'ofType' =>  null
                    ]
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'file',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'content',
                'description' =>  'Comment for ProductMediaGalleryEntriesContent on content field',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'ProductMediaGalleryEntriesContent',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'video_content',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'ProductMediaGalleryEntriesVideoContent',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'SCALAR',
        'name' =>  'Boolean',
        'description' =>  'The `Boolean` scalar type represents `true` or `false`.',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'ProductMediaGalleryEntriesContent',
        'description' =>  'Comment for ProductMediaGalleryEntriesContent.',
        'fields' =>  [
            [
                'name' =>  'base64_encoded_data',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'name',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'ProductMediaGalleryEntriesVideoContent',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'media_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'video_provider',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'video_url',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'video_title',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'video_description',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'video_metadata',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'ProductTierPrices',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'customer_group_id',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'qty',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'value',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'percentage_value',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'website_id',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'ProductPrices',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'minimalPrice',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'Price',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'maximalPrice',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'Price',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'regularPrice',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'Price',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'Price',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'amount',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'Money',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'adjustments',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'OBJECT',
                        'name' =>  'PriceAdjustment',
                        'ofType' =>  null
                    ]
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'Money',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'value',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'currency',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'CurrencyEnum',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'ENUM',
        'name' =>  'CurrencyEnum',
        'description' =>  '',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'AFN',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'GBP',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'EUR',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'INR',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'USD',
                'description' =>  '',
                'isDeprecated' =>  false
            ]
        ],
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'PriceAdjustment',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'amount',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'OBJECT',
                    'name' =>  'Money',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'code',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'PriceAdjustmentCodesEnum',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'description',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'PriceAdjustmentDescriptionEnum',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'ENUM',
        'name' =>  'PriceAdjustmentCodesEnum',
        'description' =>  '',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'TAX',
                'description' =>  '',
                'isDeprecated' =>  false
            ]
        ],
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'ENUM',
        'name' =>  'PriceAdjustmentDescriptionEnum',
        'description' =>  '',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'INCLUDED',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'EXCLUDED',
                'description' =>  '',
                'isDeprecated' =>  false
            ]
        ],
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'SearchResultPageInfo',
        'description' =>  'Comment for SearchResultPageInfo',
        'fields' =>  [
            [
                'name' =>  'page_size',
                'description' =>  'Comment for page_size',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'current_page',
                'description' =>  'Comment for current_page',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'SimpleProduct',
        'description'=> 'Comment for empty SimpleProduct type',
        'fields'=> [
            [
                'name'=> 'options',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'INTERFACE',
                        'name'=> 'CustomizableOptionInterface',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'url_key',
                'description'=> 'comment for url_key for simple product that implements [ProductInterface]',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'url_path',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'id',
                'description'=> 'comment for [ProductInterface].',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'name',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'special_price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'special_from_date',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'attribute_set_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'tier_price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'category_ids',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'SCALAR',
                        'name'=> 'Int',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'updated_at',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'country_of_manufacture',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'type_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'website_ids',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'SCALAR',
                        'name'=> 'Int',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'category_links',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'ProductCategoryLinks',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_links',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'INTERFACE',
                        'name'=> 'ProductLinksInterface',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'media_gallery_entries',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'MediaGalleryEntry',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'tier_prices',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'ProductTierPrices',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'ProductPrices',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'manufacturer',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'ProductInterface',
                'ofType'=> null
            ],
            [
                'kind'=> 'INTERFACE',
                'name'=> 'PhysicalProductInterface',
                'ofType'=> null
            ],
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableProductInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind' =>  'INTERFACE',
        'name' =>  'PhysicalProductInterface',
        'description' =>  'Comment for empty PhysicalProductInterface',
        'fields' =>  [

        ],
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  [
            [
                'kind' =>  'OBJECT',
                'name' =>  'SimpleProduct',
                'ofType' =>  null
            ]
        ]
    ],
    [
        'kind' =>  'INTERFACE',
        'name' =>  'CustomizableProductInterface',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'options',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'LIST',
                    'name' =>  null,
                    'ofType' =>  [
                        'kind' =>  'INTERFACE',
                        'name' =>  'CustomizableOptionInterface',
                        'ofType' =>  null
                    ]
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  [
            [
                'kind' =>  'OBJECT',
                'name' =>  'SimpleProduct',
                'ofType' =>  null
            ],
            [
                'kind' =>  'OBJECT',
                'name' =>  'VirtualProduct',
                'ofType' =>  null
            ]
        ]
    ],
    [
        'kind' =>  'INTERFACE',
        'name' =>  'CustomizableOptionInterface',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'title',
                'description' =>  'Comment for CustomizableOptionInterface',
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'required',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Boolean',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'sort_order',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  null,
        'possibleTypes' =>  [
            [
                'kind' =>  'OBJECT',
                'name' =>  'CustomizableAreaOption',
                'ofType' =>  null
            ],
            [
                'kind' =>  'OBJECT',
                'name' =>  'CustomizableDateOption',
                'ofType' =>  null
            ],
            [
                'kind' =>  'OBJECT',
                'name' =>  'CustomizableDropDownOption',
                'ofType' =>  null
            ],
            [
                'kind' =>  'OBJECT',
                'name' =>  'CustomizableFieldOption',
                'ofType' =>  null
            ],
            [
                'kind' =>  'OBJECT',
                'name' =>  'CustomizableFileOption',
                'ofType' =>  null
            ],
            [
                'kind' =>  'OBJECT',
                'name' =>  'CustomizableRadioOption',
                'ofType' =>  null
            ]
        ]
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'ProductLinks',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'sku',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'link_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'linked_product_sku',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'linked_product_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'position',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [
            [
                'kind' =>  'INTERFACE',
                'name' =>  'ProductLinksInterface',
                'ofType' =>  null
            ]
        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableAreaOption',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'title',
                'description'=> 'Comment for title field for CustomizableAreaOption concrete type',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'required',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Boolean',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'value',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'CustomizableAreaValue',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableOptionInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'CustomizableAreaValue',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'price',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'price_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'PriceTypeEnum',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'sku',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'max_characters',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Int',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind' =>  'ENUM',
        'name' =>  'PriceTypeEnum',
        'description' =>  '',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'FIXED',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'PERCENT',
                'description' =>  '',
                'isDeprecated' =>  false
            ],
            [
                'name' =>  'DYNAMIC',
                'description' =>  '',
                'isDeprecated' =>  false
            ]
        ],
        'possibleTypes' =>  null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableDateOption',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'title',
                'description'=> 'This description should override interface comment.',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'required',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Boolean',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'value',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'CustomizableDateValue',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableOptionInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind' =>  'OBJECT',
        'name' =>  'CustomizableDateValue',
        'description' =>  '',
        'fields' =>  [
            [
                'name' =>  'price',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'Float',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'price_type',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'ENUM',
                    'name' =>  'PriceTypeEnum',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ],
            [
                'name' =>  'sku',
                'description' =>  null,
                'args' =>  [

                ],
                'type' =>  [
                    'kind' =>  'SCALAR',
                    'name' =>  'String',
                    'ofType' =>  null
                ],
                'isDeprecated' =>  false,
                'deprecationReason' =>  null
            ]
        ],
        'inputFields' =>  null,
        'interfaces' =>  [

        ],
        'enumValues' =>  null,
        'possibleTypes' =>  null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableDropDownOption',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'title',
                'description'=> 'Comment for CustomizableOptionInterface',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'required',
                'description'=> 'Comment for required field for CustomizableDropDownOption concrete type',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Boolean',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'value',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'CustomizableDropDownValue',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableOptionInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableDropDownValue',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'option_type_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price_type',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'ENUM',
                    'name'=> 'PriceTypeEnum',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'title',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [

        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableFieldOption',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'title',
                'description'=> 'Comment for CustomizableOptionInterface',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'required',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Boolean',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> 'Comment for sort_order for CustomizableFieldOption concrete type',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'value',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'CustomizableFieldValue',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableOptionInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableFieldValue',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price_type',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'ENUM',
                    'name'=> 'PriceTypeEnum',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'max_characters',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [

        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableFileOption',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'title',
                'description'=> 'Comment for CustomizableOptionInterface',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'required',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Boolean',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'value',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'CustomizableFileValue',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableOptionInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableFileValue',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price_type',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'ENUM',
                    'name'=> 'PriceTypeEnum',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'file_extension',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'image_size_x',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'image_size_y',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [

        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableRadioOption',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'title',
                'description'=> 'Comment for CustomizableOptionInterface',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'required',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Boolean',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'value',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'CustomizableRadioValue',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableOptionInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'CustomizableRadioValue',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'option_type_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price_type',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'ENUM',
                    'name'=> 'PriceTypeEnum',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'title',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sort_order',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [

        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind'=> 'OBJECT',
        'name'=> 'VirtualProduct',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'options',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'INTERFACE',
                        'name'=> 'CustomizableOptionInterface',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'url_key',
                'description'=> 'comment for url_key inside ProductInterface type.',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'url_path',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'id',
                'description'=> 'comment for [ProductInterface].',
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'name',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'sku',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'special_price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'special_from_date',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'attribute_set_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'tier_price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Float',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'category_ids',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'SCALAR',
                        'name'=> 'Int',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'updated_at',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'country_of_manufacture',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'type_id',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'String',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'website_ids',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'SCALAR',
                        'name'=> 'Int',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'category_links',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'ProductCategoryLinks',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'product_links',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'INTERFACE',
                        'name'=> 'ProductLinksInterface',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'media_gallery_entries',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'MediaGalleryEntry',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'tier_prices',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'LIST',
                    'name'=> null,
                    'ofType'=> [
                        'kind'=> 'OBJECT',
                        'name'=> 'ProductTierPrices',
                        'ofType'=> null
                    ]
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'price',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'OBJECT',
                    'name'=> 'ProductPrices',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'manufacturer',
                'description'=> null,
                'args'=> [

                ],
                'type'=> [
                    'kind'=> 'SCALAR',
                    'name'=> 'Int',
                    'ofType'=> null
                ],
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ]
        ],
        'inputFields'=> null,
        'interfaces'=> [
            [
                'kind'=> 'INTERFACE',
                'name'=> 'ProductInterface',
                'ofType'=> null
            ],
            [
                'kind'=> 'INTERFACE',
                'name'=> 'CustomizableProductInterface',
                'ofType'=> null
            ]
        ],
        'enumValues'=> null,
        'possibleTypes'=> null
    ],
    [
        'kind' => 'OBJECT',
        'name' => 'EntityUrl',
        'description' => '',
        'fields' => [
            [
                'name' => 'id',
                'description' => null,
                'args' => [

                ],
                'type' => [
                    'kind' => 'SCALAR',
                    'name' => 'Int',
                    'ofType' => null
                ],
                'isDeprecated' => false,
                'deprecationReason' => null
            ],
            [
                'name' => 'canonical_url',
                'description' => null,
                'args' => [

                ],
                'type' => [
                    'kind' => 'SCALAR',
                    'name' => 'String',
                    'ofType' => null
                ],
                'isDeprecated' => false,
                'deprecationReason' => null
            ],
            [
                'name' => 'type',
                'description' => null,
                'args' => [

                ],
                'type' => [
                    'kind' => 'ENUM',
                    'name' => 'UrlRewriteEntityTypeEnum',
                    'ofType' => null
                ],
                'isDeprecated' => false,
                'deprecationReason' => null
            ]
        ],
        'inputFields' => null,
        'interfaces' => [

        ],
        'enumValues' => null,
        'possibleTypes' => null
    ],
    [
        'kind' => 'ENUM',
        'name' => 'UrlRewriteEntityTypeEnum',
        'description' => 'Comment for empty Enum',
        'fields' => null,
        'inputFields' => null,
        'interfaces' => null,
        'enumValues' => [

        ],
        'possibleTypes' => null
    ]
];
