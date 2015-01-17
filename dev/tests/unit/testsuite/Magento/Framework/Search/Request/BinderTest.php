<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

use Magento\TestFramework\Helper\ObjectManager;

class BinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Request\Binder
     */
    private $binder;

    /**
     * SetUP method
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->binder = $helper->getObject('Magento\Framework\Search\Request\Binder');
    }

    /**
     * Test for method "build"
     */
    public function testBind()
    {
        $requestData = [
            'dimensions' => ['scope' => ['value' => '$sss$']],
            'queries' => ['query' => ['value' => '$query$']],
            'filters' => ['filter' => ['from' => '$from$', 'to' => '$to$', 'value' => '$filter$']],
            'aggregations' => ['price' => ['method' => '$method$']],
            'from' => 0,
            'size' => 15,
        ];
        $bindData = [
            'dimensions' => ['scope' => 'default'],
            'placeholder' => [
                '$query$' => 'match_query',
                '$from$' => 'filter_from',
                '$to$' => 'filter_to',
                '$filter$' => 'filter_value',
                '$method$' => 'filter_method',
            ],
            'from' => 1,
            'size' => 10,
        ];
        $expectedResult = [
            'dimensions' => ['scope' => ['value' => 'default']],
            'queries' => ['query' => ['value' => 'match_query']],
            'filters' => ['filter' => ['from' => 'filter_from', 'to' => 'filter_to', 'value' => 'filter_value']],
            'aggregations' => ['price' => ['method' => 'filter_method']],
            'from' => 1,
            'size' => 10,
        ];

        $result = $this->binder->bind($requestData, $bindData);

        $this->assertEquals($result, $expectedResult);
    }
}
