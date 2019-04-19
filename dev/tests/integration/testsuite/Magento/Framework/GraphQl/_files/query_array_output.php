<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'Query' =>
        [
            'name' => 'Query',
            'fields' =>
                [
                    'customAttributeMetadata' =>
                        [
                            'name' => 'customAttributeMetadata',
                            'type' => 'CustomAttributeMetadata',
                            'arguments' =>
                                [
                                    'attributes' =>
                                        [
                                            'name' => 'attributes',
                                            'type' => 'AttributeInput',
                                            'description' => '',
                                            'required' => true,
                                            'isList' => true,
                                            'itemsRequired' => true,
                                            'defaultValue' => null
                                        ],

                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => Magento\EavGraphQl\Model\Resolver\CustomAttributeMetadata::class,
                                'description' => 'Returns the attribute type, given an attribute code and entity type',
                                'cache' => [
                                    'cacheTag' => 'cat_test',
                                    'cacheIdentity' =>
                                        Magento\EavGraphQl\Model\Resolver\CustomAttributeMetadata::class
                                ]
                        ],
                        'products' => [
                        'name' => 'products',
                        'type' => 'Products',
                        'arguments' =>
                            [
                                'search' =>
                                    [
                                        'name' => 'search',
                                        'type' => 'String',
                                        'description' => '',
                                        'required' => false,
                                        'isList' => false,
                                        'itemsRequired' => false,
                                        'defaultValue' => null
                                    ],

                                    'filter' =>
                                    [
                                        'name' => 'filter',
                                        'type' => 'ProductFilterInput',
                                        'description' => '',
                                        'required' => false,
                                        'isList' => false,
                                        'itemsRequired' => false,
                                        'defaultValue' => null
                                    ],

                                    'pageSize' =>
                                    [
                                        'name' => 'pageSize',
                                        'type' => 'Int',
                                        'description' => 'number of records to display',
                                        'required' => false,
                                        'isList' => false,
                                        'itemsRequired' => false,
                                        'defaultValue' => 10
                                    ],

                                    'currentPage' =>
                                    [
                                        'name' => 'currentPage',
                                        'type' => 'Int',
                                        'description' => '',
                                        'required' => false,
                                        'isList' => false,
                                        'itemsRequired' => false,
                                        'defaultValue' => 1
                                    ],
                                    'sort' =>
                                    [
                                        'name' => 'sort',
                                        'type' => 'ProductSortInput',
                                        'description' => '',
                                        'required' => false,
                                        'isList' => false,
                                        'itemsRequired' => false,
                                        'defaultValue' => null
                                    ]
                            ],
                            'required' => false,
                            'isList' => false,
                            'resolver' => Magento\CatalogGraphQl\Model\Resolver\Products::class,
                            'description' => 'comment for products fields'
                        ]
                ]
        ],
        'PriceAdjustmentDescriptionEnum' =>
        [
            'name' => 'PriceAdjustmentDescriptionEnum',
            'values' =>
                [
                    'INCLUDED' =>
                        [
                            'name' => 'included',
                            'value' => 'INCLUDED',
                            'description' => 'price is included'
                        ],
                        'EXCLUDED' =>
                        [
                            'name' => 'excluded',
                            'value' => 'EXCLUDED',
                            'description' => 'price is excluded'
                        ]
                ],
                'description' => 'Description for enumType PriceAdjustmentDescriptionEnum'
        ],
        'ProductLinks' =>
        [
            'name' => 'ProductLinks',
            'fields' =>
                [
                    'sku' =>
                        [
                            'name' => 'sku',
                            'type' => 'String',

                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => 'The identifier of the linked product',

                        ],
                        'link_type' =>
                        [
                            'name' => 'link_type',
                            'type' => 'String',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => '',

                        ],
                        'linked_product_sku' =>
                        [
                            'name' => 'linked_product_sku',
                            'type' => 'String',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => 'The SKU of the linked product',

                        ],
                        'linked_product_type' =>
                        [
                            'name' => 'linked_product_type',
                            'type' => 'String',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => '',

                        ],
                        'position' =>
                        [
                            'name' => 'position',
                            'type' => 'Int',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => 'The position within the list of product links',

                        ]
                ],
                'interfaces' =>
                [
                    'ProductLinksInterface' =>
                        [
                            'interface' => 'ProductLinksInterface',
                            'copyFields' => true,
                        ],
                ],
                'description' => 'ProductLinks is an implementation of ProductLinksInterface.'
        ],
        'ProductLinksInterface' =>
        [
            'name' => 'ProductLinksInterface',
            'fields' =>
                [
                    'sku' =>
                        [
                            'name' => 'sku',
                            'type' => 'String',

                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => 'The identifier of the linked product',

                        ],
                        'link_type' =>
                        [
                            'name' => 'link_type',
                            'type' => 'String',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => '',

                        ],
                        'linked_product_sku' =>
                        [
                            'name' => 'linked_product_sku',
                            'type' => 'String',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => 'The SKU of the linked product',

                        ],
                        'linked_product_type' =>
                        [
                            'name' => 'linked_product_type',
                            'type' => 'String',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => '',

                        ],
                        'position' =>
                        [
                            'name' => 'position',
                            'type' => 'Int',
                            'arguments' =>
                                [
                                ],
                                'required' => false,
                                'isList' => false,
                                'resolver' => '',
                                'description' => 'The position within the list of product links',

                        ]
                ],
                'typeResolver' => Magento\CatalogGraphQl\Model\ProductLinkTypeResolverComposite::class,
                'description' => 'description for ProductLinksInterface'
        ]
];
