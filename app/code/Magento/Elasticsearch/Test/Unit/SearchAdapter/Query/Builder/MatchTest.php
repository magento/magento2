<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as MatchQueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerInterface;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\Search\Request\Query\Match as MatchRequestQuery;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class MatchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MatchQueryBuilder
     */
    private $matchQueryBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $valueTransformerPoolMock = $this->createMock(ValueTransformerPool::class);
        $valueTransformerMock = $this->createMock(ValueTransformerInterface::class);
        $valueTransformerPoolMock->method('get')
            ->willReturn($valueTransformerMock);
        $valueTransformerMock->method('transform')
            ->willReturnArgument(0);

        $this->matchQueryBuilder = (new ObjectManager($this))->getObject(
            MatchQueryBuilder::class,
            [
                'fieldMapper' => $this->getFieldMapper(),
                'preprocessorContainer' => [],
                'valueTransformerPool' => $valueTransformerPoolMock,
            ]
        );
    }

    /**
     * Tests that method constructs a correct select query.
     *
     * @see MatchQueryBuilder::build
     */
    public function testBuild()
    {
        $rawQueryValue = 'query_value';
        $selectQuery = $this->matchQueryBuilder->build([], $this->getMatchRequestQuery($rawQueryValue), 'not');

        $expectedSelectQuery = [
            'bool' => [
                'must_not' => [
                    [
                        'match' => [
                            'some_field' => [
                                'query' => $rawQueryValue,
                                'boost' => 43,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedSelectQuery, $selectQuery);
    }

    /**
     * Tests that method constructs a correct "match" query depending on query value.
     *
     * @dataProvider matchProvider
     *
     * @param string $rawQueryValue
     * @param string $queryValue
     * @param string $match
     */
    public function testBuildMatchQuery($rawQueryValue, $queryValue, $match)
    {
        $query = $this->matchQueryBuilder->build([], $this->getMatchRequestQuery($rawQueryValue), 'should');

        $expectedSelectQuery = [
            'bool' => [
                'should' => [
                    [
                        $match => [
                            'some_field' => [
                                'query' => $queryValue,
                                'boost' => 43,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expectedSelectQuery,
            $query,
            sprintf('Wrong "match" query. Should be processed with "%s"', $match)
        );
    }

    /**
     * @return array
     */
    public function matchProvider()
    {
        return [
            ['query_value', 'query_value', 'match'],
            ['"query value"', 'query value', 'match_phrase'],
        ];
    }

    /**
     * Gets fieldMapper mock object.
     *
     * @return FieldMapperInterface|MockObject
     */
    private function getFieldMapper()
    {
        $fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->getMockForAbstractClass();

        $fieldMapper->method('getFieldName')
            ->with('some_field', ['type' => FieldMapperInterface::TYPE_QUERY])
            ->willReturnArgument(0);

        return $fieldMapper;
    }

    /**
     * Gets RequestQuery mock object.
     *
     * @param string $rawQueryValue
     * @return MatchRequestQuery|MockObject
     */
    private function getMatchRequestQuery($rawQueryValue)
    {
        $matchRequestQuery = $this->getMockBuilder(MatchRequestQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $matchRequestQuery->method('getValue')
            ->willReturn($rawQueryValue);
        $matchRequestQuery->method('getMatches')
            ->willReturn([['field' => 'some_field', 'boost' => 42]]);

        return $matchRequestQuery;
    }
}
