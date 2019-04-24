<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'bool_query' => [
        'dimensions' => [
            'scope' =>
                [
                    'name' => 'scope',
                    'value' => 'default_override',
                ],
        ],
        'queries' => [
            'bool_query' => [
                'name' => 'bool_query',
                'boost' => '10',
                'queryReference' => [
                    0 => [
                        'clause' => 'must',
                        'ref' => 'must_query',
                    ],
                    1 => [
                        'clause' => 'should',
                        'ref' => 'should_query',
                    ],
                    2 => [
                        'clause' => 'not',
                        'ref' => 'not_query',
                    ],
                ],
                'type' => 'boolQuery',
            ],
            'match_query' => [
                'value' => '$match_term_override$',
                'name' => 'match_query',
                'boost' => '1',
                'match' => [
                    0 => [
                        'field' => 'match_field',
                    ],
                    1 => [
                        'field' => '*',
                    ],
                    2 => [
                        'field' => 'match_field_3',
                    ],
                ],
                'type' => 'matchQuery',
            ],
            'must_query' => [
                'name' => 'must_query',
                'boost' => '1',
                'filterReference' =>  [
                    0 => [
                        'clause' => 'must',
                        'ref' => 'must_filter',
                    ],
                ],
                'type' => 'filteredQuery',
            ],
            'should_query' => [
                'name' => 'should_query',
                'boost' => '1',
                'filterReference' => [
                    0 => [
                        'clause' => 'should',
                        'ref' => 'should_filter',
                    ],
                ],
                'type' => 'filteredQuery',
            ],
            'not_query' => [
                'name' => 'not_query',
                'boost' => '1',
                'filterReference' => [
                    0 => [
                        'clause' => 'not',
                        'ref' => 'not_filter',
                    ],
                ],
                'type' => 'filteredQuery',
            ],
            'match_query_2' => [
                'value' => '$match_term_override$',
                'boost' => '1',
                'name' => 'match_query_2',
                'match' => [
                    0 => [
                        'field' => 'match_field_4',
                    ],
                ],
                'type' => 'matchQuery',
            ],
        ],
        'filters' => [
            'must_filter' => [
                'name' => 'must_filter',
                'field' => 'field_1_override',
                'value' => '$value_1_override$',
                'type' => 'termFilter',
            ],
            'should_filter' => [
                'name' => 'should_filter',
                'field' => 'field_2_override',
                'from' => '$value_2_override.from$',
                'to' => '$value_2_override.to$',
                'type' => 'rangeFilter',
            ],
            'not_filter' => [
                'name' => 'not_filter',
                'field' => 'field_3_override',
                'value' => '$field_3_override$',
                'type' => 'wildcardFilter',
            ],
        ],
        'aggregations' => [
            'bucket_1' => [
                'name' => 'bucket_1',
                'field' => 'field_1_override',
                'method' => '$field_1_dynamic_algorithm_override$',
                'metric' => [
                    0 =>
                        [
                            'type' => 'count',
                        ],
                ],
                'type' => 'dynamicBucket',
            ],
            'bucket_2' => [
                'name' => 'bucket_2',
                'field' => 'field_2_override',
                'metric' => [
                    0 =>
                        [
                            'type' => 'count',
                        ],
                ],
                'type' => 'termBucket',
            ],
            'bucket_3' => [
                'name' => 'bucket_3',
                'field' => 'field_2_override',
                'range' => [
                    0 =>
                        [
                            'from' => '$value_2_override.from$',
                            'to' => 'value_2_override.to$',
                        ],
                ],
                'type' => 'rangeBucket',
            ],
        ],
        'from' => '0',
        'size' => '10000',
        'query' => 'bool_query',
        'index' => 'bool_query_index_override',
    ],
    'filter_query' => [
        'dimensions' => [
            'scope' =>
                [
                    'name' => 'scope',
                    'value' => 'default_override',
                ],
        ],
        'queries' => [
            'filter_query' => [
                'name' => 'filter_query',
                'boost' => '1',
                'filterReference' => [
                    0 =>
                        [
                            'clause' => 'must',
                            'ref' => 'bool_filter',
                        ],
                ],
                'type' => 'filteredQuery',
            ],
        ],
        'filters' => [
            'bool_filter' => [
                'name' => 'bool_filter',
                'filterReference' => [
                    0 => [
                        'clause' => 'must',
                        'ref' => 'must_filter',
                    ],
                    1 => [
                        'clause' => 'should',
                        'ref' => 'should_filter',
                    ],
                    2 => [
                        'clause' => 'not',
                        'ref' => 'not_filter',
                    ],
                ],
                'type' => 'boolFilter',
            ],
            'must_filter' => [
                'name' => 'must_filter',
                'field' => 'field_1',
                'value' => '$value_1$',
                'type' => 'termFilter',
            ],
            'should_filter' => [
                'name' => 'should_filter',
                'field' => 'field_2',
                'from' => '$value_2.from$',
                'to' => '$value_2.to$',
                'type' => 'rangeFilter',
            ],
            'not_filter' => [
                'name' => 'not_filter',
                'field' => 'field_3',
                'value' => '$field_3$',
                'type' => 'wildcardFilter',
            ],
        ],
        'from' => '0',
        'size' => '10000',
        'query' => 'filter_query',
        'index' => 'filter_query_index_override',
        'aggregations' => [],
    ],
    'new_match_query' => [
        'dimensions' => [
            'scope' =>
                [
                    'name' => 'scope',
                    'value' => 'new',
                ],
        ],
        'queries' => [
            'new_match_query' => [
                'value' => '$match_term$',
                'name' => 'new_match_query',
                'boost' => '1',
                'match' => [
                    0 =>
                        [
                            'field' => 'new_match_field',
                        ],
                ],
                'type' => 'matchQuery',
            ],
        ],
        'filters' => [],
        'from' => '0',
        'size' => '10000',
        'query' => 'new_match_query',
        'index' => 'new_query_index',
        'aggregations' => [],
    ],
];
