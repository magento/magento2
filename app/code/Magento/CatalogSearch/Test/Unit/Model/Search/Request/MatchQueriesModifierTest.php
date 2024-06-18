<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\Request;

use Magento\CatalogSearch\Model\Search\Request\MatchQueriesModifier;
use PHPUnit\Framework\TestCase;

/**
 * Test match queries modifier
 */
class MatchQueriesModifierTest extends TestCase
{
    /**
     * Test that queries configuration are merged into request
     *
     * @param array $queries
     * @param array $requests
     * @param array $expected
     * @dataProvider modifyDataProvider
     */
    public function testModify(array $queries, array $requests, array $expected): void
    {
        $model = new MatchQueriesModifier($queries);
        $this->assertEquals($expected, $model->modify($requests));
    }

    /**
     * @return array
     */
    public static function modifyDataProvider(): array
    {
        return [
            [
                [
                    'partial_search' => [
                        'name' => [
                            'analyzer' => 'standard',
                            'max_expansions' => 20,
                        ]
                    ],
                ],
                [
                    'search_1' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ],
                        'queries' => [
                            'partial_search' => [
                                'name' => 'partial_search',
                                'value' => '$search_term$',
                                'match' => [
                                    [
                                        'field' => '*'
                                    ],
                                    [
                                        'field' => 'sku',
                                        'matchCondition' => 'match_phrase_prefix',
                                    ],
                                    [
                                        'field' => 'name',
                                        'matchCondition' => 'match_phrase_prefix',
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'search_2' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ]
                    ]
                ],
                [
                    'search_1' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ],
                        'queries' => [
                            'partial_search' => [
                                'name' => 'partial_search',
                                'value' => '$search_term$',
                                'match' => [
                                    [
                                        'field' => '*'
                                    ],
                                    [
                                        'field' => 'sku',
                                        'matchCondition' => 'match_phrase_prefix',
                                    ],
                                    [
                                        'field' => 'name',
                                        'matchCondition' => 'match_phrase_prefix',
                                        'analyzer' => 'standard',
                                        'max_expansions' => 20,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'search_2' => [
                        'filters' => [
                            'category_filter' => [
                                'name' => 'category_filter',
                                'field' => 'category_ids',
                                'value' => '$category_ids$',
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
