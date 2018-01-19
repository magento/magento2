<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    [
        "Query" =>
            [
                "name" => "Query",
                "type" => "graphql_type",
                "fields" =>
                    [
                        "customAttributeMetadata" =>
                            [
                                "name" => "customAttributeMetadata",
                                "type" => "CustomAttributeMetadata",
                                "required" => false,
                                "resolver" => "Magento\\EavGraphQl\\Model\\Resolver\\CustomAttributeMetadata",
                                "arguments" =>
                                    [
                                        "attributes" =>
                                            [
                                                "type" => "ObjectArrayArgument",
                                                "name" => "attributes",
                                                "itemType" => "AttributeInput",
                                                "itemsRequired" => "true",
                                                "required" => "true"
                                            ]

                                    ]

                            ]

                    ]

            ],

            "CustomAttributeMetadata" =>
            [
                "name" => "CustomAttributeMetadata",
                "type" => "graphql_type",
                "fields" =>
                    [
                        "items" =>
                            [
                                "name" => "items",
                                "type" => "ObjectArrayOutputField",
                                "required" => false,
                                "itemType" => "Attribute",
                                "arguments" =>
                                    [
                                    ]

                            ]

                    ]

            ],

            "Attribute" =>
            [
                "name" => "Attribute",
                "type" => "graphql_type",
                "fields" =>
                    [
                        "attribute_code" =>
                            [
                                "name" => "attribute_code",
                                "type" => "String",
                                "required" => "",
                                "arguments" =>
                                    [
                                    ]

                            ],

                            "entity_type" =>
                            [
                                "name" => "entity_type",
                                "type" => "String",
                                "required" => "",
                                "arguments" =>
                                    [
                                    ]

                            ],

                            "attribute_type" =>
                            [
                                "name" => "attribute_type",
                                "type" => "String",
                                "required" => "",
                                "arguments" =>
                                    [
                                    ]

                            ]

                    ]

            ],
            "AttributeInput" => [
            "name" => "AttributeInput",
            "type" => "graphql_input",
            "fields" => [
                "attribute_code" =>
                    [
                        "name" => "attribute_code",
                        "type" => "String",
                        "required" => "",
                        "arguments" =>
                            [
                            ]

                    ],

                    "entity_type" =>
                    [
                        "name" => "entity_type",
                        "type" => "String",
                        "required" => "",
                        "arguments" =>
                            [
                            ]

                    ]

            ]

              ]

    ]
];
