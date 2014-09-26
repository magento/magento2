<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return [
    "suggested_search_container" => [
        "dimensions" => [
            "scope" => [
                "name" => "scope",
                "value" => "default"
            ]
        ],
        "queries" => [
            "suggested_search_container" => [
                "name" => "suggested_search_container",
                "boost" => "2",
                "queryReference" => [
                    [
                        "clause" => "must",
                        "ref" => "fulltext_search_query"
                    ],
                    [
                        "clause" => "should",
                        "ref" => "fulltext_search_query2"
                    ]
                ],
                "type" => "boolQuery"
            ],
            "fulltext_search_query" => [
                "name" => "fulltext_search_query",
                "boost" => "5",
                "value" => "default_value",
                "match" => [
                    [
                        "field" => "title",
                        "boost" => "2"
                    ],
                    [
                        "field" => "description"
                    ]
                ],
                "type" => "matchQuery"
            ],
            "fulltext_search_query2" => [
                "name" => "fulltext_search_query2",
                "filterReference" => [
                    [
                        "ref" => "promoted"
                    ]
                ],
                "type" => "filteredQuery"
            ]
        ],
        "filters" => [
            "promoted" => [
                "name" => "promoted",
                "filterReference" => [
                    [
                        "clause" => "must",
                        "ref" => "price_name"
                    ],
                    [
                        "clause" => "should",
                        "ref" => "price_name1"
                    ]
                ],
                "type" => "boolFilter"
            ],
            "price_name" => [
                "field" => "promoted_boost",
                "name" => "price_name",
                "from" => "10",
                "to" => "100",
                "type" => "rangeFilter"
            ],
            "price_name1" => [
                "name" => "price_name1",
                "field" => "price_name",
                "value" => "\$name",
                "type" => "termFilter"
            ]
        ],
        "aggregations" => [
            "category_bucket" => [
                "name" => "category_bucket",
                "field" => "category",
                "metric" => [
                    [
                        "type" => "sum",
                    ],
                    [
                        "type" => "count",
                    ],
                    [
                        "type" => "min",
                    ],
                    [
                        "type" => "max",
                    ]
                ],
                "type" => "termBucket",
            ],
            "price_bucket" => [
                "name" => "price_bucket",
                "field" => "price",
                "metric" => [
                    [
                        "type" => "sum",
                    ],
                    [
                        "type" => "count",
                    ],
                    [
                        "type" => "min",
                    ],
                    [
                        "type" => "max",
                    ]
                ],
                "range" => [
                    [
                        "from" => "",
                        "to" => "50",
                    ],
                    [
                        "from" => "50",
                        "to" => "100",
                    ],
                    [
                        "from" => "100",
                        "to" => "",
                    ],
                ],
                "type" => "rangeBucket",
            ]
        ],
        "from" => "10",
        "size" => "10",
        "query" => "suggested_search_container",
        "index" => "product"
    ]
];
