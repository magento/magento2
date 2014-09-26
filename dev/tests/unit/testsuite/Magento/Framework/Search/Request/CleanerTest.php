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
namespace Magento\Framework\Search\Request;

use Magento\TestFramework\Helper\ObjectManager;

class CleanerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Request\Cleaner
     */
    private $cleaner;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->cleaner = $helper->getObject('Magento\Framework\Search\Request\Cleaner');
    }

    public function testClean()
    {
        $requestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [
                        ['ref' => 'bool_query_rm'],
                        ['ref' => 'match_query'],
                        ['ref' => 'filtered_query_to_filter2']
                    ],
                    'type' => 'boolQuery'
                ],
                'match_query' => ['value' => 'ok', 'type' => 'matchQuery'],
                'bool_query_rm' => [
                    'queryReference' => [
                        ['ref' => 'match_query_rm'],
                        ['ref' => 'filtered_query_to_query'],
                        ['ref' => 'filtered_query_to_filter']
                    ],
                    'type' => 'boolQuery'
                ],
                'match_query_rm' => ['value' => '$some$', 'type' => 'matchQuery'],
                'match_query_rm2' => ['value' => '$some2$', 'type' => 'matchQuery'],
                'filtered_query_to_query' => [
                    'queryReference' => [['ref' => 'match_query_rm2']],
                    'type' => 'filteredQuery'
                ],
                'filtered_query_to_filter' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery'
                ],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery'
                ]
            ],
            'filters' => [
                'bool_filter' => [
                    'filterReference' => [['ref' => 'term_filter'], ['ref' => 'range_filter']],
                    'type' => 'boolFilter'
                ],
                'term_filter' => ['value' => '$val$', 'type' => 'termFilter'],
                'range_filter' => ['from' => '$from$', 'to' => '$to$', 'type' => 'rangeFilter'],
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter'
                ],
                'term_filter2' => ['value' => 'value_good', 'type' => 'termFilter']
            ]
        ];
        $exceptedRequestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [['ref' => 'match_query'], ['ref' => 'filtered_query_to_filter2']],
                    'type' => 'boolQuery'
                ],
                'match_query' => ['value' => 'ok', 'type' => 'matchQuery'],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery'
                ]
            ],
            'filters' => [
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter'
                ],
                'term_filter2' => ['value' => 'value_good', 'type' => 'termFilter']
            ]
        ];

        $result = $this->cleaner->clean($requestData);

        $this->assertEquals($exceptedRequestData, $result);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Reference is not provided
     */
    public function testCleanFilteredQueryType()
    {
        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'type' => 'filteredQuery'
                ],
            ],
            'filters' => []
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid query type
     */
    public function testCleanQueryType()
    {
        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'type' => 'fQuery'
                ],
            ],
            'filters' => []
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid filter type
     */
    public function testCleanFilterType()
    {
        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'filterReference' => [['ref' => 'filter']],
                    'type' => 'filteredQuery'
                ],
            ],
            'filters' => [
                'filter' => [
                    'type' => 'fType'
                ]
            ]
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cycle found. Query filtered_query already used in request hierarchy
     */
    public function testCleanQueryCycle()
    {
        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'queryReference' => [['ref' => 'filtered_query']],
                    'type' => 'boolQuery'
                ],
            ],
            'filters' => []
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testCleanFilterCycle()
    {
        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery'
                ],
            ],
            'filters' => [
                'bool_filter' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'boolFilter'
                ]
            ]
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Filter bool_filter does not exist
     */
    public function testCleanFilterNotFound()
    {
        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery'
                ],
            ],
            'filters' => []
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Query test does not exist
     */
    public function testCleanQueryNotExists()
    {
        $requestData = [
            'query' => 'test',
            'queries' => [],
            'filters' => []
        ];

        $this->cleaner->clean($requestData);
    }
}
