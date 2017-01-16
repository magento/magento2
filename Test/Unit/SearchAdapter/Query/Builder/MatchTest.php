<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Match
     */
    protected $model;

    /**
     * @var FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var QueryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryInterface;

    /**
     * @var PreprocessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $preprocessorInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fieldMapper = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->preprocessorInterface = $this
            ->getMockBuilder(\Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Query\Builder\Match::class,
            [
                'fieldMapper' => $this->fieldMapper,
                'preprocessorContainer' => [$this->preprocessorInterface],
            ]
        );
    }

    /**
     * Test build() method
     */
    public function testBuild()
    {
        $expectedResult = [
            'bool' => [
                'must_not' => [
                    [
                        'match' => [
                            'some_field' => [
                                'query' => 'query_value',
                                'boost' => 43,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->fieldMapper->expects($this->once())
            ->method('getFieldName')
            ->with('some_field', ['type' => FieldMapperInterface::TYPE_QUERY])
            ->willReturnArgument(0);

        /** @var \Magento\Framework\Search\Request\Query\Match|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
            ->setMethods(['getValue', 'getMatches'])
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('getValue')->willReturn('query_value');
        $query->expects($this->once())->method('getMatches')->willReturn([['field' => 'some_field', 'boost' => 42]]);

        $this->preprocessorInterface->expects($this->any())
            ->method('process')
            ->with('query_value')
            ->willReturn('query_value');
        $this->assertEquals($expectedResult, $this->model->build([], $query, 'not'));
    }
}
