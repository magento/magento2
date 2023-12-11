<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Request;

use Magento\Framework\Search\Request\Binder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class BinderTest extends TestCase
{
    /**
     * @var Binder
     */
    private $binder;

    /**
     * SetUP method
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->binder = $helper->getObject(Binder::class);
    }

    /**
     * Test for method "build"
     */
    public function testBind()
    {
        $requestData = [
            'dimensions' => ['scope' => ['value' => '$sss$']],
            'queries' => [
                'query' => ['value' => '$query$'],
                'empty_query' => ['value' => '$empty_query$'],
                'space_query' => ['value' => '$space_query$'],
                'zero_value_query' => ['name' => 'zero_value', 'type' => 'filteredQuery'],
            ],
            'filters' => [
                'filter' => ['from' => '$from$', 'to' => '$to$', 'value' => '$filter$'],
                'zero_value_filter' => [
                    'type' => 'termFilter',
                    'name' => 'zero_value',
                    'field' => 'zero_value',
                    'value' => '$zero_value$',
                ],
            ],
            'aggregations' => ['price' => ['method' => '$method$']],
            'from' => 0,
            'size' => 15,
        ];
        $bindData = [
            'dimensions' => ['scope' => 'default'],
            'placeholder' => [
                '$query$' => 'match_query',
                '$empty_query$' => '  ',
                '$space_query$' => '  value',
                '$from$' => 'filter_from',
                '$to$' => 'filter_to',
                '$filter$' => 'filter_value',
                '$method$' => 'filter_method',
                '$zero_value$' => '0',
            ],
            'from' => 1,
            'size' => 10,
        ];
        $expectedResult = [
            'dimensions' => ['scope' => ['value' => 'default']],
            'queries' => [
                'query' => ['value' => 'match_query', 'is_bind' => true],
                'empty_query' => ['value' => '$empty_query$'],
                'space_query' => ['value' => 'value', 'is_bind' => true],
                'zero_value_query' => ['name' => 'zero_value', 'type' => 'filteredQuery'],
            ],
            'filters' => [
                'filter' => [
                    'from' => 'filter_from',
                    'to' => 'filter_to',
                    'value' => 'filter_value',
                    'is_bind' => true
                ],
                'zero_value_filter' => [
                    'type' => 'termFilter',
                    'name' => 'zero_value',
                    'field' => 'zero_value',
                    'value' => '0',
                    'is_bind' => true,
                ]
            ],
            'aggregations' => ['price' => ['method' => 'filter_method', 'is_bind' => true]],
            'from' => 1,
            'size' => 10,
        ];

        $result = $this->binder->bind($requestData, $bindData);

        $this->assertEquals($expectedResult, $result);
    }
}
