<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\QueryChecker;

use Magento\CatalogSearch\Model\Search\QueryChecker\FullTextSearchCheck;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FullTextSearchCheckTest extends TestCase
{
    /**
     * @var FullTextSearchCheck
     */
    private $fullTextSearchCheck;

    protected function setUp(): void
    {
        $this->fullTextSearchCheck = (new ObjectManager($this))
            ->getObject(FullTextSearchCheck::class);
    }

    /**
     * @param $query
     * @param $errorMsg
     *
     * @dataProvider positiveDataProvider
     */
    public function testPositiveCheck($query, $errorMsg)
    {
        $this->assertTrue(
            $this->fullTextSearchCheck->isRequiredForQuery($query),
            $errorMsg
        );
    }

    /**
     * @param $query
     * @param $errorMsg
     *
     * @dataProvider negativeDataProvider
     */
    public function testNegativeCheck($query, $errorMsg)
    {
        $this->assertFalse(
            $this->fullTextSearchCheck->isRequiredForQuery($query),
            $errorMsg
        );
    }

    public function testInvalidArgumentException1()
    {
        $this->expectException('InvalidArgumentException');
        $matchQueryMock = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMockForAbstractClass();

        $matchQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn('42');

        $this->fullTextSearchCheck->isRequiredForQuery($matchQueryMock);
    }

    public function testInvalidArgumentException2()
    {
        $this->expectException('InvalidArgumentException');
        $filterMock = $this->getFilterQueryMock();

        $filterMock->expects($this->any())
            ->method('getReferenceType')
            ->willReturn('42');

        $this->fullTextSearchCheck->isRequiredForQuery($filterMock);
    }

    /**
     * @return array
     */
    public function positiveDataProvider()
    {
        $boolQueryMock = $this->getBoolQueryMock();

        $boolQueryMock->expects($this->any())
            ->method('getShould')
            ->willReturn([]);

        $boolQueryMock->expects($this->any())
            ->method('getMust')
            ->willReturn([$this->getMatchQueryMock()]);

        $filterMock = $this->getFilterQueryMock();

        $filterMock->expects($this->any())
            ->method('getReferenceType')
            ->willReturn(Filter::REFERENCE_QUERY);

        $filterMock->expects($this->any())
            ->method('getReference')
            ->willReturn($this->getMatchQueryMock());

        return [
            [
                $this->getMatchQueryMock(),
                'Testing match query'
            ], [
                $boolQueryMock,
                'Testing bool query'
            ], [
                $filterMock,
                'Testing filter query'
            ]
        ];
    }

    /**
     * @return array
     */
    public function negativeDataProvider()
    {
        $emptyBoolQueryMock = $this->getBoolQueryMock();

        $emptyBoolQueryMock->expects($this->any())
            ->method('getShould')
            ->willReturn([]);

        $emptyBoolQueryMock->expects($this->any())
            ->method('getMust')
            ->willReturn([]);

        $emptyBoolQueryMock->expects($this->any())
            ->method('getMustNot')
            ->willReturn([]);

        $filterMock = $this->getFilterQueryMock();

        $filterMock->expects($this->any())
            ->method('getReferenceType')
            ->willReturn(Filter::REFERENCE_FILTER);

        return [
            [
                $emptyBoolQueryMock,
                'Testing bool query'
            ], [
                $filterMock,
                'Testing filter query'
            ]
        ];
    }

    /**
     * @return MockObject
     */
    private function getMatchQueryMock()
    {
        $matchQueryMock = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMockForAbstractClass();

        $matchQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_MATCH);

        return $matchQueryMock;
    }

    /**
     * @return MockObject
     */
    private function getBoolQueryMock()
    {
        $boolQueryMock = $this->getMockBuilder(BoolExpression::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getShould', 'getMust', 'getMustNot'])
            ->getMock();

        $boolQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_BOOL);

        return $boolQueryMock;
    }

    /**
     * @return MockObject
     */
    private function getFilterQueryMock()
    {
        $filterQueryMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getReferenceType', 'getReference'])
            ->getMock();

        $filterQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_FILTER);

        return $filterQueryMock;
    }
}
