<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
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
                'isDeprecated'=> true,
                'deprecationReason'=> 'Deprecated url_path test'
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
        'kind' =>  'ENUM',
        'name' =>  'SortEnum',
        'description' =>  'Comment for SortEnum',
        'fields' =>  null,
        'inputFields' =>  null,
        'interfaces' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'ASC',
                'description' =>  'Ascending Order',
                'isDeprecated' =>  false,
                'deprecationReason' => ''
            ],
            [
                'name' =>  'DESC',
                'description' =>  '',
                'isDeprecated' =>  true,
                'deprecationReason' => 'Deprecated SortEnum Value test'
            ]
        ],
        'possibleTypes' =>  null
    ],
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
];
