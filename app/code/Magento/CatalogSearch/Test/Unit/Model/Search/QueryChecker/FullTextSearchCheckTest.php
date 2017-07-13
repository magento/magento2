<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\QueryChecker;

class FullTextSearchCheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Search\QueryChecker\FullTextSearchCheck
     */
    private $fullTextSearchCheck;

    public function setUp()
    {
        $this->fullTextSearchCheck = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\CatalogSearch\Model\Search\QueryChecker\FullTextSearchCheck::class);
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentException1()
    {
        $matchQueryMock = $this->getMockBuilder(\Magento\Framework\Search\Request\QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMockForAbstractClass();

        $matchQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn('42');

        $this->fullTextSearchCheck->isRequiredForQuery($matchQueryMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentException2()
    {
        $filterMock = $this->getFilterQueryMock();

        $filterMock->expects($this->any())
            ->method('getReferenceType')
            ->willReturn('42');

        $this->fullTextSearchCheck->isRequiredForQuery($filterMock);
    }

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
            ->willReturn(\Magento\Framework\Search\Request\Query\Filter::REFERENCE_QUERY);

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
            ->willReturn(\Magento\Framework\Search\Request\Query\Filter::REFERENCE_FILTER);

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

    private function getMatchQueryMock()
    {
        $matchQueryMock = $this->getMockBuilder(\Magento\Framework\Search\Request\QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType'])
            ->getMockForAbstractClass();

        $matchQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn(\Magento\Framework\Search\Request\QueryInterface::TYPE_MATCH);

        return $matchQueryMock;
    }

    private function getBoolQueryMock()
    {
        $boolQueryMock = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\BoolExpression::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getShould', 'getMust', 'getMustNot'])
            ->getMock();

        $boolQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn(\Magento\Framework\Search\Request\QueryInterface::TYPE_BOOL);

        return $boolQueryMock;
    }

    private function getFilterQueryMock()
    {
        $filterQueryMock = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getReferenceType', 'getReference'])
            ->getMock();

        $filterQueryMock->expects($this->any())
            ->method('getType')
            ->willReturn(\Magento\Framework\Search\Request\QueryInterface::TYPE_FILTER);

        return $filterQueryMock;
    }
}
